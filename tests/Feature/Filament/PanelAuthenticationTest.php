<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../Support/PublicPageAssertions.php';

it('keeps the admin panel behind authentication', function () {
    withVite();

    $manifest = assetManifest();

    $response = get('/admin');

    $response
        ->assertRedirect('/admin/login');
    $response = get('/admin/login');

    $response
        ->assertOk()
        ->assertSeeHtml($manifest['resources/css/filament/admin/theme.css']['file'])
        ->assertSeeHtml('<link rel="icon" href="/images/elephant-small-32.png" />')
        ->assertSee('Appearance')
        ->assertSee('Enable light theme')
        ->assertSee('Enable dark theme')
        ->assertSee('Enable system theme');
});
