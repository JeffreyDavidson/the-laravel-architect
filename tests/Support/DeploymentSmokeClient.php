<?php

namespace Tests\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class DeploymentSmokeClient
{
    public static function request(string $baseUrl, string|false $clientId = false, string|false $clientSecret = false): PendingRequest
    {
        if (! in_array($baseUrl, ['https://thelaravelarchitect.com', 'https://staging.thelaravelarchitect.com'], true)) {
            throw new InvalidArgumentException('Smoke checks require an approved HTTPS origin.');
        }

        $request = Http::baseUrl($baseUrl)
            ->connectTimeout(5)
            ->timeout(15)
            ->withoutRedirecting();

        if ($baseUrl === 'https://staging.thelaravelarchitect.com') {
            if (! $clientId || ! $clientSecret) {
                throw new InvalidArgumentException('Staging smoke checks require Cloudflare Access credentials.');
            }

            $request->withHeaders([
                'CF-Access-Client-Id' => $clientId,
                'CF-Access-Client-Secret' => $clientSecret,
            ]);
        }

        return $request;
    }
}
