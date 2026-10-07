<?php

use App\View\Composers\StructuredDataComposer;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

// Generated URLs use a fixed origin so the expectations do not depend on APP_URL.
beforeEach(function () {
    URL::forceRootUrl('https://example.test');
    URL::forceScheme('https');
});

it('gives the view structured data for the current route', function () {
    $route = Route::getRoutes()
        ->getByName('uses');
    if (! $route instanceof RoutingRoute) {
        throw new RuntimeException('The uses route is not registered.');
    }
    request()->setRouteResolver(fn (): RoutingRoute => $route);
    $view = view('pages.uses');

    app(StructuredDataComposer::class)
        ->compose($view);

    expect($view->getData()['structuredData'] ?? null)->toMatchArray([
        1 => [
            '@type' => 'WebPage',
            '@id' => 'https://example.test/uses#page',
            'name' => 'Uses',
            'url' => 'https://example.test/uses',
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => 'https://example.test#website',
            ],
        ],
    ]);
});
