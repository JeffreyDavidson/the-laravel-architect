<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/channels*' => Http::response([
            'items' => [
                ['statistics' => ['subscriberCount' => 1234]],
            ],
        ]),
    ]);
});

it('renders the core public pages', function (string $uri, string $copy) {
    $this->get($uri)
        ->assertOk()
        ->assertSee($copy, false);
})->with([
    ['/', 'The Laravel Architect'],
    ['/about', 'About'],
    ['/contact', 'Contact'],
    ['/uses', 'Uses'],
    ['/blog', 'Blog'],
    ['/projects', 'Projects'],
    ['/podcasts', 'Podcasts'],
]);

it('keeps the admin panel behind authentication', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/login')->assertOk();
});
