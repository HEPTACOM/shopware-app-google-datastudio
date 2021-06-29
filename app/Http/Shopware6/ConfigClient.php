<?php

declare(strict_types=1);

namespace App\Http\Shopware6;

use Vin\ShopwareSdk\Client\CreateClientTrait;
use Vin\ShopwareSdk\Data\Context;

class ConfigClient
{
    use CreateClientTrait;

    public function __construct()
    {
        $this->createHttpClient();
    }

    public function getShopwareVersion(Context $context): ?string
    {
        return $this->decodeResponse($this->getHttpClient()->get($context->apiEndpoint . '/api/_info/config', [
            'headers' => $this->buildHeaders($context),
        ])->getBody()->getContents())['version'] ?? null;
    }

    protected function buildHeaders(Context $context, array $additionalHeaders = []): array
    {
        $accessToken = $context->accessToken;

        $headers = array_merge([
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/json',
            'Authorization' => $accessToken->tokenType . ' ' . $accessToken->accessToken,
            'sw-language-id' => $context->languageId,
            'sw-currency-id' => $context->currencyId,
            'sw-version-id' => $context->versionId,
            'sw-inheritance' => $context->inheritance,
            'sw-api-compatibility' => $context->compatibility,
        ], $additionalHeaders);

        return array_filter($headers);
    }

    private function decodeResponse(string $response): array
    {
        return \json_decode($response, true) ?? [];
    }
}
