<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

final class AddSecurityHeaders
{
    private const array HEADERS = [
        'Permissions-Policy' => 'camera=(), geolocation=(), microphone=()',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response instanceof Response) {
            throw new UnexpectedValueException('The HTTP middleware pipeline did not return a response.');
        }

        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        }

        foreach (self::HEADERS as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        if ($request->isSecure() && ! $response->headers->has('Strict-Transport-Security')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $scriptSources = [
            "'self'",
            "'unsafe-inline'",
            "'unsafe-eval'",
            'https://cdn.usefathom.com',
            'https://challenges.cloudflare.com',
        ];
        $connectSources = [
            "'self'",
            'https://api.usefathom.com',
            'https://cdn.usefathom.com',
            'https://challenges.cloudflare.com',
        ];

        if (app()->isLocal()) {
            array_push(
                $scriptSources,
                'http://localhost:*',
                'http://127.0.0.1:*',
                'https://localhost:*',
                'https://127.0.0.1:*',
            );
            array_push(
                $connectSources,
                'http://localhost:*',
                'http://127.0.0.1:*',
                'https://localhost:*',
                'https://127.0.0.1:*',
                'ws://localhost:*',
                'ws://127.0.0.1:*',
                'wss://localhost:*',
                'wss://127.0.0.1:*',
            );
        }

        $directives = [
            'base-uri' => ["'self'"],
            'connect-src' => $connectSources,
            'default-src' => ["'self'"],
            'font-src' => ["'self'", 'data:'],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'self'"],
            'frame-src' => ['https://challenges.cloudflare.com', 'https://www.youtube-nocookie.com'],
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
