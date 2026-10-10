<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

/**
 * Adds the content security policy and the other security headers to every response. Public
 * pages get a per-request script nonce; admin pages (Filament and Livewire need inline and
 * eval'd scripts) get 'unsafe-inline' and 'unsafe-eval' instead. Admin, preview and error
 * responses are kept out of caches and search indexes.
 *
 * Each site adds its own third-party hosts and paths through `security-headers`.
 * Turnstile and the local Vite dev server are always allowed.
 */
final class AddSecurityHeaders
{
    private const array HEADERS = [
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Resource-Policy' => 'same-origin',
        'Permissions-Policy' => 'camera=(), geolocation=(), microphone=()',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
    ];

    private const string TURNSTILE_HOST = 'https://challenges.cloudflare.com';

    /** @param  Closure(Request): mixed  $next */
    public function handle(Request $request, Closure $next): Response
    {
        if (! self::isAdminRequest($request)) {
            Vite::useCspNonce();
        }

        $response = $next($request);

        if (! $response instanceof Response) {
            throw new UnexpectedValueException('The HTTP middleware pipeline did not return a response.');
        }

        return self::apply($response, $request);
    }

    /**
     * Add the security headers to a response, including exception responses
     * rendered before this middleware runs.
     */
    public static function apply(Response $response, Request $request): Response
    {
        $isAdminRequest = self::isAdminRequest($request);

        if (! $response->headers->has('Content-Security-Policy')) {
            $scriptNonce = $isAdminRequest
                ? null
                : Vite::cspNonce() ?? Vite::useCspNonce();

            $response->headers->set('Content-Security-Policy', self::contentSecurityPolicy($scriptNonce));
        }

        foreach (self::HEADERS as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        $hsts = Config::get('security-headers.hsts');

        if ($request->isSecure() && is_string($hsts) && $hsts !== '' && ! $response->headers->has('Strict-Transport-Security')) {
            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        if ($isAdminRequest || self::isNoindexRequest($request) || $response->getStatusCode() >= 400) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    private static function isAdminRequest(Request $request): bool
    {
        $patterns = [];

        foreach (self::stringList('security-headers.admin_paths') as $path) {
            $path = trim($path, '/');

            if ($path === '') {
                continue;
            }

            $patterns[] = $path;
            $patterns[] = "{$path}/*";
        }

        return $patterns !== [] && $request->is(...$patterns);
    }

    private static function isNoindexRequest(Request $request): bool
    {
        $patterns = self::stringList('security-headers.noindex_paths');

        return $patterns !== [] && $request->is(...$patterns);
    }

    private static function contentSecurityPolicy(?string $scriptNonce): string
    {
        $scriptSources = [
            "'self'",
            ...$scriptNonce === null
                ? ["'unsafe-inline'", "'unsafe-eval'"]
                : ["'nonce-{$scriptNonce}'"],
            self::TURNSTILE_HOST,
            ...self::extraSources('script_src'),
        ];
        $connectSources = [
            "'self'",
            self::TURNSTILE_HOST,
            ...self::extraSources('connect_src'),
        ];

        if (App::isLocal()) {
            $scriptSources[] = 'http://localhost:*';
            $scriptSources[] = 'http://127.0.0.1:*';
            $scriptSources[] = 'https://localhost:*';
            $scriptSources[] = 'https://127.0.0.1:*';
            $connectSources[] = 'http://localhost:*';
            $connectSources[] = 'http://127.0.0.1:*';
            $connectSources[] = 'https://localhost:*';
            $connectSources[] = 'https://127.0.0.1:*';
            $connectSources[] = 'ws://localhost:*';
            $connectSources[] = 'ws://127.0.0.1:*';
            $connectSources[] = 'wss://localhost:*';
            $connectSources[] = 'wss://127.0.0.1:*';
        }

        $directives = [
            'base-uri' => ["'self'"],
            'connect-src' => $connectSources,
            'default-src' => ["'self'"],
            'font-src' => ["'self'", 'data:'],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'self'"],
            'frame-src' => [self::TURNSTILE_HOST, ...self::extraSources('frame_src')],
            'img-src' => ["'self'", 'data:', 'blob:', 'https:', ...self::extraSources('img_src')],
            'media-src' => ["'self'", 'blob:', 'https:', ...self::extraSources('media_src')],
            'object-src' => ["'none'"],
            'script-src' => $scriptSources,
            'style-src' => ["'self'", "'unsafe-inline'"],
            'worker-src' => ["'self'", 'blob:'],
        ];

        return collect($directives)
            ->map(fn (array $sources, string $directive): string => "{$directive} ".implode(' ', $sources))
            ->implode('; ');
    }

    /** @return list<string> */
    private static function extraSources(string $directive): array
    {
        return self::stringList("security-headers.csp.{$directive}");
    }

    /**
     * A config list with anything that isn't a non-empty string dropped, so a bad entry can
     * never be written into a header.
     *
     * @return list<string>
     */
    private static function stringList(string $key): array
    {
        $values = Config::get($key, []);

        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(
            $values,
            fn (mixed $value): bool => is_string($value) && trim($value) !== '',
        ));
    }
}
