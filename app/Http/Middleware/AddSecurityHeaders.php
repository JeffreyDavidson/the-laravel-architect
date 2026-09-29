<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

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

        if ($request->isSecure() && ! $response->headers->has('Strict-Transport-Security')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if ($isAdminRequest || $request->is('preview', 'preview/*') || $response->getStatusCode() >= 400) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    private static function isAdminRequest(Request $request): bool
    {
        $adminPath = trim(Filament::getPanel('admin')->getPath(), '/');

        return $request->is($adminPath, "{$adminPath}/*");
    }

    private static function contentSecurityPolicy(?string $scriptNonce): string
    {
        $scriptSources = [
            "'self'",
            ...$scriptNonce === null
                ? ["'unsafe-inline'", "'unsafe-eval'"]
                : ["'nonce-{$scriptNonce}'"],
            'https://challenges.cloudflare.com',
        ];
        $connectSources = [
            "'self'",
            'https://challenges.cloudflare.com',
        ];

        if (app()->isLocal()) {
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
            'frame-src' => [
                'https://challenges.cloudflare.com',
                'https://www.youtube-nocookie.com',
                'https://open.spotify.com',
                'https://embed.podcasts.apple.com',
                'https://share.transistor.fm',
            ],
            'img-src' => ["'self'", 'data:', 'blob:', 'https:'],
            'media-src' => ["'self'", 'blob:', 'https:'],
            'object-src' => ["'none'"],
            'script-src' => $scriptSources,
            'style-src' => ["'self'", "'unsafe-inline'"],
            'worker-src' => ["'self'", 'blob:'],
        ];

        return collect($directives)
            ->map(fn (array $sources, string $directive): string => $directive.' '.implode(' ', $sources))
            ->implode('; ');
    }
}
