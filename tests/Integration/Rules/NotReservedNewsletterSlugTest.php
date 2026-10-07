<?php

use App\Rules\NotReservedNewsletterSlug;
use Illuminate\Support\Facades\Validator;

covers(NotReservedNewsletterSlug::class);

it('rejects a slug taken by a static newsletter route', function (string $slug) {
    $validator = Validator::make(['slug' => $slug], ['slug' => [new NotReservedNewsletterSlug]]);

    expect($validator->errors()
        ->get('slug'))
        ->toBe(['This slug is already used by another newsletter page. Choose a different slug.']);
})->with(['confirmed', 'rss']);

it('accepts a slug that no static newsletter route uses', function (string $slug) {
    $validator = Validator::make(['slug' => $slug], ['slug' => [new NotReservedNewsletterSlug]]);

    expect($validator->passes())
        ->toBeTrue();
})->with([
    'ordinary issue slug' => ['first-issue'],
    'route segment followed by a parameter' => ['unsubscribe'],
]);
