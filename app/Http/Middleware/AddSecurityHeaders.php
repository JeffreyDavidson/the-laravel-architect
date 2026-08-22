<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

final class AddSecurityHeaders
{
    private const array HEADERS = [
        'Content-Security-Policy' => "base-uri 'self'; frame-ancestors 'self'; object-src 'none'",
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
}
