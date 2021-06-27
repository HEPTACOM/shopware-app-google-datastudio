<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Shopware6\Shop;
use Illuminate\Http\Request;
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

    protected function getAppName(): string
    {
        return (string) \config('heptaconnect-shopware-six.app_name', 'HeptacomHeptaconnectCloudDataAnalysis');
    }

    protected function getAppSecret(): string
    {
        return (string) \config('heptaconnect-shopware-six.app_secret', 'mysecret');
    }
}
