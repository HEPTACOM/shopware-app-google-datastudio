<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Shopware6\ConfigClient;
use App\Models\EntityRepository;
use App\Models\Shopware6\Shop;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Vin\ShopwareSdk\Client\AdminAuthenticator;
use Vin\ShopwareSdk\Client\GrantType\ClientCredentialsGrantType;
use Vin\ShopwareSdk\Data\Context;
use Vin\ShopwareSdk\Data\Criteria;
use Vin\ShopwareSdk\Data\Entity\Order\OrderDefinition;
use Vin\ShopwareSdk\Data\Filter\EqualsFilter;
use Vin\ShopwareSdk\Data\Filter\RangeFilter;
use Vin\ShopwareSdk\Factory\RepositoryFactory;
use Vin\ShopwareSdk\Repository\EntityRepository as BaseEntityRepository;

class Shopware6Controller extends Controller
{
    public function register(Request $request): BaseResponse
    {
        $shopId = (string) $request->get('shop-id');
        $shopUrl = (string) $request->get('shop-url');
        $timestamp = (int) $request->get('timestamp');
        $receivedSignature = (string) $request->header('shopware-app-signature');
        $calculatedSignature = \hash_hmac('sha256', $request->getQueryString(), $this->getAppSecret());

        // TODO verify signatures
        // TODO verify timestamp

        $proof = \hash_hmac('sha256', $shopId . $shopUrl . $this->getAppName(), $this->getAppSecret());
        $shopSecret = Str::uuid()->toString();

        /** @var Shop $shop */
        $shop = Shop::create([
            'shop_id' => $shopId,
            'shop_url' => $shopUrl,
            'shop_secret' => $shopSecret,
        ]);

        return Response::json([
            'proof' => $proof,
            'secret' => $shopSecret,
            'confirmation_url' => \route('api.v1.shopware6.confirm', $shop->id),
        ]);
    }

    public function confirm(string $internalId, Request $request): BaseResponse
    {
        $apiKey = (string) $request->get('apiKey');
        $secretKey = (string) $request->get('secretKey');
        $timestamp = (int) $request->get('timestamp');
        $shopUrl = (string) $request->get('shopUrl');
        $shopId = (string) $request->get('shopId');
        // TODO verify timestamp

        $shop = Shop::where('shop_id', $shopId)
            ->where('shop_url', $shopUrl)
            ->where('id', $internalId)
            ->whereNull('api_key')
            ->whereNull('secret_key')
            ->first();

        if ($shop instanceof Shop) {
            $shop->api_key = $apiKey;
            $shop->secret_key = $secretKey;
            $shop->saveOrFail();

            return Response::noContent();
        }

        return Response::noContent(BaseResponse::HTTP_BAD_REQUEST);
    }

    public function wizard(Request $request)
    {
        $shopId = $request->get('shop-id');
        $shopUrl = $request->get('shop-url');
        /** @var Shop|null $shop */
        $shop = Shop::where('shop_id', $shopId)
            ->where('shop_url', $shopUrl)
            ->whereNotNull('api_key')
            ->whereNotNull('secret_key')
            ->first();

        $shopVersion = (string) $request->get('sw-version');

        if ($shopVersion === '') {
            $shopVersion = (new ConfigClient())->getShopwareVersion($this->getContextFromShop($shop));
        }

        return Response::view('integration.shopware-6.wizard', [
            'shop' => $shop,
            'swVersion' => $shopVersion,
        ]);
    }

    protected function getAppName(): string
    {
        return (string) \config('heptaconnect-shopware-six.app_name', 'HeptacomHeptaconnectCloudDataAnalysis');
    }

    protected function getAppSecret(): string
    {
        return (string) \config('heptaconnect-shopware-six.app_secret', 'mysecret');
    }

    public function order(Shop $shop, Request $request): BaseResponse
    {
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');

        $lockKey = join('_', [
            'lock',
            $shop->shop_id,
            $startDate,
            $endDate,
        ]);

        $lock = Cache::lock($lockKey);
        $ttl = 60;

        try {
            $orders = $lock->block($ttl, function () use ($shop, $startDate, $endDate, $ttl) {
                $cacheKey = join('_', [
                    'cache',
                    $shop->shop_id,
                    $startDate,
                    $endDate,
                ]);

                if (!Cache::has($cacheKey)) {
                    $context = $this->getContextFromShop($shop);
                    $orders = iterable_to_array($this->getOrders($startDate, $endDate, $context));

                    Cache::put($cacheKey, $orders, $ttl);
                }

                return Cache::get($cacheKey);
            });

            return Response::json($orders);
        } catch (LockTimeoutException $exception) {
            return Response::json([], 423);
        }
    }

    public function appLifecycleDeleted(Request $request)
    {
        $source = $request->get('source');

        $jsonPayload = \json_encode($request->json()->all());
        $shopUrl = $source['url'] ?? null;
        $shopId = $source['shopId'] ?? null;
        $theirSignature = $request->header('shopware-shop-signature') ?? null;

        if (!\is_string($shopUrl)) {
            logger('Tried to uninstall Shopware 6 app, but no url was provided.');

            return;
        }

        if (!\is_string($shopId)) {
            logger('Tried to uninstall Shopware 6 app, but no shop-id was provided.');

            return;
        }

        $builder = Shop::where('shop_id', $shopId);

        /** @var Shop $shop */
        foreach ($builder->getModels() as $shop) {
            $ourSignature = hash_hmac('sha256', $jsonPayload, $shop->shop_secret);

            if ($theirSignature === $ourSignature) {
                $builder->delete();

                return;
            }
        }
    }

    protected function getOrders($startDate, $endDate, Context $context): iterable
    {
        /** @var BaseEntityRepository $repository */
        $repository = RepositoryFactory::create(OrderDefinition::ENTITY_NAME);
        $orderRepository = new EntityRepository($repository);

        $criteria = (new Criteria())
            ->addFilter(new RangeFilter('orderDate', [
                RangeFilter::GTE => $startDate,
                RangeFilter::LTE => $endDate,
            ]))
            ->addAssociations([
                'billingAddress.country',
                'deliveries.shippingOrderAddress.country',
                'salesChannel',
                'language',
                'transactions.paymentMethod',
                'orderCustomer.customer.group',
            ])
        ;

        $criteria->setPage(0);
        $criteria->setLimit(0);

        $criteria_0_0_24 = clone $criteria;
        $criteria->addFilter(new EqualsFilter('transactions.stateMachineState.technicalName', 'paid'));

        try {
            $orders = $orderRepository->search($criteria, $context)->getData();
        } catch (\Throwable $_) {
            $orders = $orderRepository->search($criteria_0_0_24, $context)->getData();
        }

        /** @var array $order */
        foreach ($orders as $order) {
            $dateTime = \date_create($order['orderDate']);

            if ($dateTime === false) {
                $dateTime = null;
            }

            yield [
                'shippingCostsNet' => $order['shippingCosts']['totalPrice'] ?? null, // TODO: tax
                'shippingCostsGross' => $order['shippingCosts']['totalPrice'] ?? null, // TODO: tax
                'shippingCity' => $order['deliveries'][0]['shippingOrderAddress']['city'] ?? null,
                'shippingCountry' => $order['deliveries'][0]['shippingOrderAddress']['country']['iso'] ?? null,
                'billingCity' => $order['billingAddress']['city'] ?? null,
                'billingCountry' => $order['billingAddress']['country']['iso'] ?? null,
                'customerNumber' => $order['orderCustomer']['customerNumber'] ?? null,
                'customerAffiliate' => ($order['orderCustomer']['customer'] ?? [])['affiliateCode'] ?? null,
                'customerGroup' => $order['orderCustomer']['customer']['group']['name'] ?? null,
                'customerOrigin' => '', // TODO: save referrer and read it
                'salesChannel' => $order['salesChannel']['name'] ?? null,
                'language' => $order['language']['name'] ?? null,
                'voucherNumber' => null, // TODO: voucher
                'voucherAmount' => .0, // TODO: voucher
                'paymentMethod' => $order['transactions'][0]['paymentMethod']['name'] ?? null,
                'orderNumber' => $order['orderNumber'] ?? null,
                'totalAmountNet' => $order['amountNet'] ?? null,
                'totalAmountGross' => $order['amountTotal'] ?? null,
                'orderTime' => $dateTime ? $dateTime->format('YmdHis') : null,
            ];
        }
    }

    /**
     * @param Shop $shop
     * @return Context
     * @throws \Vin\ShopwareSdk\Exception\AuthorizationFailedException
     */
    private function getContextFromShop(Shop $shop): Context
    {
        $apiKey = $shop->api_key;
        $secretKey = $shop->secret_key;
        $shopUrl = \rtrim((string) $shop->shop_url, '/');
        $grantType = new ClientCredentialsGrantType($apiKey, $secretKey);

        $adminClient = new AdminAuthenticator($grantType, $shopUrl);
        $accessToken = $adminClient->fetchAccessToken();
        $context = new Context($shopUrl, $accessToken);
        return $context;
    }
}
