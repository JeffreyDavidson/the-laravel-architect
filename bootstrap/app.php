<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ValidateSignature;
use JeffreyDavidson\CreatorKit\Http\Middleware\AddSecurityHeaders;
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
        // Check signed links before loading their models, so an unsigned link is
        // rejected the same way whether or not its draft or subscriber exists.
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: ValidateSignature::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);

        // Responses rendered before the middleware runs, such as maintenance
        // mode, still need the security and private-response headers.
        $exceptions->respond(
            fn (Response $response, Throwable $_exception, Request $request): Response => AddSecurityHeaders::apply($response, $request)
        );
    })->create();
