<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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

        $proof = \hash_hmac('sha256', $shopId . $shopUrl . $this->getAppName(), $this->getAppSecret());
        $shopSecret = Str::uuid()->toString();

        return Response::json([
            'proof' => $proof,
            'secret' => $shopSecret,
            'confirmation_url' => \route('api.v1.shopware6.confirm'),
        ]);
    }

    public function confirm(): BaseResponse
    {
        return Response::noContent();
    }

    protected function getAppName(): string
    {
        return (string) \config('heptaconnect-shopware-six.app_name', 'HeptacomHeptaconnectCloud');
    }

    protected function getAppSecret(): string
    {
        return (string) \config('heptaconnect-shopware-six.app_secret', 'mysecret');
    }
}
