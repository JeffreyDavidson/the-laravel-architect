<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TurnstileVerifier
{
    public function passes(Request $request, string $expectedAction): bool
    {
        $token = $request->string('cf-turnstile-response')->toString();
        $secret = config('services.turnstile.secret_key');
        $endpoint = config('services.turnstile.siteverify_url');

        if (
            $token === ''
            || $expectedAction === ''
            || ! is_string($secret)
            || trim($secret) === ''
            || ! is_string($endpoint)
            || trim($endpoint) === ''
        ) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post($endpoint, [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);
        } catch (Throwable $exception) {
            Log::warning('Turnstile verification request failed.', [
                'exception' => $exception::class,
            ]);

            return false;
        }

        $hostname = $response->json('hostname');

        return $response->ok()
            && $response->json('success') === true
            && $response->json('action') === $expectedAction
            && is_string($hostname)
            && in_array(
                $this->normalizeHostname($hostname),
                $this->allowedHostnames(),
                true,
            );
    }

    /**
     * @return list<string>
     */
    private function allowedHostnames(): array
    {
        $hostnames = config('services.turnstile.allowed_hostnames', []);

        if (! is_array($hostnames)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $hostname): string => is_string($hostname)
                ? $this->normalizeHostname($hostname)
                : '',
            $hostnames,
        )));
    }

    private function normalizeHostname(string $hostname): string
    {
        return strtolower(rtrim(trim($hostname), '.'));
    }
}
