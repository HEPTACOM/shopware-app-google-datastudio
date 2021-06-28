<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Shopware6\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

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

        $shop = Shop::where('shop_id', $shopId)
            ->where('shop_url', $shopUrl)
            ->whereNotNull('api_key')
            ->whereNotNull('secret_key')
            ->first();

        return Response::view('integration.shopware-6.wizard', [
            'shop' => $shop,
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

    public function order(Shop $shop,Request $request): BaseResponse
    {
        $shopAttributes = $shop->getAttributes();
        $apiKey = $shopAttributes['api_key'] ?? null;
        $secretKey = $shopAttributes['secret_key'] ?? null;
        $shopUrl = $shopAttributes['shop_url'] ?? null;

        $response = Http::post($shopUrl . '/api/oauth/token', [
            'client_id' => $apiKey,
            'client_secret' => $secretKey,
            'grant_type' => 'client_credentials'
        ]);

        if ($response->status() === 200) {
            $responseBody = $response->json();
            $accessToken = $responseBody['access_token'] ?? null;

            if ($accessToken) {
                $orderResponse = Http::withHeaders([
                    'authorization' => 'Bearer ' . $accessToken,
                    'accept'=> 'application/vnd.api+json'

                ])->post($shopUrl . '/api/search/order', [
                    'limit'=> 2,
                    'page'=> 1,
                    'total-count-mode'=> 1,
                ]);

                $orderData = $orderResponse->json();

                sleep(0);
                $orderResult = [
                    'shippingCosts' => 0.0,

                ];

                return Response::json([
                    $orderResponse->body()
                ]);
            }
        }



        return Response::json([
            'statusCode' => $response->json()
        ]);
    }
}
