<?php

use App\Support\Seo\CollectionListing;
use App\Support\Seo\JsonLd;

it('numbers breadcrumbs from one in order', function () {
    $breadcrumbs = JsonLd::breadcrumbList([
        ['name' => 'Home', 'url' => 'https://example.test'],
        ['name' => 'Blog', 'url' => 'https://example.test/blog'],
    ]);

    expect($breadcrumbs)->toBe([
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://example.test'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => 'https://example.test/blog'],
        ],
    ]);
});

it('continues collection item positions from the listing offset', function () {
    $nodes = JsonLd::collectionPage(new CollectionListing(
        'Archive',
        'https://example.test/archive?page=2',
        [['name' => 'A post', 'url' => 'https://example.test/blog/a-post']],
        positionOffset: 18,
    ));

    expect($nodes)->toBe([
        [
            '@type' => 'CollectionPage',
            '@id' => 'https://example.test/archive?page=2#collection',
            'name' => 'Archive',
            'url' => 'https://example.test/archive?page=2',
            'mainEntity' => [
                '@type' => 'ItemList',
                '@id' => 'https://example.test/archive?page=2#items',
            ],
        ],
        [
            '@type' => 'ItemList',
            '@id' => 'https://example.test/archive?page=2#items',
            'numberOfItems' => 1,
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 19, 'name' => 'A post', 'item' => 'https://example.test/blog/a-post'],
            ],
        ],
    ]);
});

it('formats seconds as an ISO 8601 duration', function (int $seconds, string $duration) {
    expect(JsonLd::isoDuration($seconds))->toBe($duration);
})->with([
    'over an hour' => [3725, 'PT1H2M5S'],
    'under a minute' => [42, 'PT0H0M42S'],
]);
