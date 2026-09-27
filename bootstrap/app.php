<?php

use App\Http\Middleware\AddSecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(AddSecurityHeaders::class);
        // One-click unsubscribe posts come from mail providers without a
        // forgery token; the signed URL authorizes them instead.
        $middleware->preventRequestForgery(except: ['newsletter/unsubscribe/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);

        // Responses rendered before the middleware runs, such as maintenance
        // mode, still need the security and private-response headers.
        $exceptions->respond(
            fn (Response $response, Throwable $_exception, Request $request): Response => AddSecurityHeaders::apply($response, $request)
        );
    })->create();
