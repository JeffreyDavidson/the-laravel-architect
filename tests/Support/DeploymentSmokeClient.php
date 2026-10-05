<?php

namespace Tests\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Throwable;

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
            // One retry after a connection failure absorbs a briefly loaded shared server.
            // HTTP error statuses are returned unchanged, so real failures still fail fast.
            // A redirect reaches the callback with no exception, so it accepts null.
            ->retry(2, 2000, fn (?Throwable $exception): bool => $exception instanceof ConnectionException, throw: false)
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
