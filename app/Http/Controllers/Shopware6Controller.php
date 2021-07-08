<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Shopware6\ConfigClient;
use App\Models\EntityRepository;
use App\Models\Shopware6\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Vin\ShopwareSdk\Client\AdminAuthenticator;
use Vin\ShopwareSdk\Client\GrantType\ClientCredentialsGrantType;
use Vin\ShopwareSdk\Data\Context;
use Vin\ShopwareSdk\Data\Criteria;
use Vin\ShopwareSdk\Data\Entity\Order\OrderDefinition;
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
        ])->withHeaders([
            'Content-Security-Policy' => 'frame-ancestors ' . $request->query('shop-url') . '/',
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
        $context = $this->getContextFromShop($shop);

        return Response::json(
            iterable_to_array($this->getOrders($startDate, $endDate, $context))
        );
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

        $orders = $orderRepository->search($criteria, $context)->getData();

        /** @var array $order */
        foreach ($orders as $order) {
            yield [
                'shippingCostsNet' => $order['shippingCosts']['totalPrice'], // TODO: tax
                'shippingCostsGross' => $order['shippingCosts']['totalPrice'], // TODO: tax
                'shippingCity' => $order['deliveries'][0]['shippingOrderAddress']['city'],
                'shippingCountry' => $order['deliveries'][0]['shippingOrderAddress']['country']['iso'],
                'billingCity' => $order['billingAddress']['city'],
                'billingCountry' => $order['billingAddress']['country']['iso'],
                'customerNumber' => $order['orderCustomer']['customerNumber'],
                'customerAffiliate' => $order['orderCustomer']['customer']['affiliateCode'],
                'customerGroup' => $order['orderCustomer']['customer']['group']['name'],
                'customerOrigin' => '', // TODO: save referrer and read it
                'salesChannel' => $order['salesChannel']['name'],
                'language' => $order['language']['name'],
                'voucherNumber' => null, // TODO: voucher
                'voucherAmount' => .0, // TODO: voucher
                'paymentMethod' => $order['transactions'][0]['paymentMethod']['name'],
                'orderNumber' => $order['orderNumber'],
                'totalAmountNet' => $order['amountNet'],
                'totalAmountGross' => $order['amountTotal'],
                'orderTime' => \date_create($order['orderDate'])->format('YmdHis'),
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
        $shopUrl = $shop->shop_url;
        $grantType = new ClientCredentialsGrantType($apiKey, $secretKey);

        $adminClient = new AdminAuthenticator($grantType, $shopUrl);
        $accessToken = $adminClient->fetchAccessToken();
        $context = new Context($shopUrl, $accessToken);
        return $context;
    }
}
