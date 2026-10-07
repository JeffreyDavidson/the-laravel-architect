<?php

use App\Models\Tag;
use App\Rules\UniqueTagSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

covers(UniqueTagSlug::class);

pest()->use(RefreshDatabase::class);

it('rejects a slug another tag uses in the current locale', function () {
    Tag::factory()->create(['slug' => 'laravel']);

    $validator = Validator::make(['slug' => 'laravel'], ['slug' => [new UniqueTagSlug]]);

    expect($validator->errors()
        ->get('slug'))
        ->toBe(['The slug has already been taken.']);
});

it('rejects a slug another tag uses while editing a different tag', function () {
    Tag::factory()->create(['slug' => 'laravel']);
    $tag = Tag::factory()->create(['slug' => 'filament']);

    $validator = Validator::make(['slug' => 'laravel'], ['slug' => [new UniqueTagSlug($tag)]]);

    expect($validator->fails())
        ->toBeTrue();
});

it('lets the tag being edited keep its own slug', function () {
    $tag = Tag::factory()->create(['slug' => 'laravel']);

    $validator = Validator::make(['slug' => 'laravel'], ['slug' => [new UniqueTagSlug($tag)]]);

    expect($validator->passes())
        ->toBeTrue();
});

it('accepts a slug no tag uses', function () {
    Tag::factory()->create(['slug' => 'laravel']);

    $validator = Validator::make(['slug' => 'filament'], ['slug' => [new UniqueTagSlug]]);

    expect($validator->passes())
        ->toBeTrue();
});
