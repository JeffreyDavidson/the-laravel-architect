<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('titles a missing page without words from its address', function (string $path, string $addressTitle) {
    $response = $this->get($path);

    $response->assertNotFound()
        ->assertSeeHtml('<title>Page not found — Jeffrey Davidson</title>')
        ->assertDontSee($addressTitle);
})->with([
    'unknown page' => ['/nope-404', 'Nope 404'],
    'unknown nested page' => ['/admin-backup/wp-login', 'Wp Login'],
    'missing blog post' => ['/blog/missing-post', 'Missing Post'],
]);
