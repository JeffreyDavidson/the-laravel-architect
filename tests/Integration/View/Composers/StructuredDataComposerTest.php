<?php

use App\View\Composers\StructuredDataComposer;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;

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
            '@id' => 'http://localhost/uses#page',
            'name' => 'Uses',
            'url' => 'http://localhost/uses',
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => 'http://localhost#website',
            ],
        ],
    ]);
});
