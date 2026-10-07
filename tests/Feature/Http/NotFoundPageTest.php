<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('titles a missing page without words from its address', function (string $path, string $addressTitle) {
    $response = get($path);

    $response->assertNotFound()
        ->assertSeeHtml('<title>Page not found — Jeffrey Davidson</title>')
        ->assertDontSee($addressTitle);
})->with([
    'unknown page' => ['/nope-404', 'Nope 404'],
    'unknown nested page' => ['/admin-backup/wp-login', 'Wp Login'],
    'missing blog post' => ['/blog/missing-post', 'Missing Post'],
]);

it('keeps a missing page out of search results without a canonical link', function () {
    $response = get('/nope');

    $response->assertNotFound()
        ->assertSeeHtml('<meta name="robots" content="noindex, nofollow">')
        ->assertDontSeeHtml('rel="canonical"');
});
