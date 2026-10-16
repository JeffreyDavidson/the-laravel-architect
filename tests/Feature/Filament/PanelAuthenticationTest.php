<?php

use App\Filament\Pages\Dashboard;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../Support/PublicPageAssertions.php';

it('keeps the admin panel behind authentication', function () {
    withVite();

    $manifest = assetManifest();

    $response = get(Dashboard::getUrl());

    $response
        ->assertRedirect(Filament::getPanel('admin')->getLoginUrl());
    $response = get(Filament::getPanel('admin')->getLoginUrl() ?? '');

    $response
        ->assertOk()
        ->assertSeeHtml($manifest['resources/css/filament/admin/theme.css']['file'])
        ->assertSeeHtml('<link rel="icon" href="/images/elephant-small-32.png" />')
        ->assertSee('Appearance')
        ->assertSee('Enable light theme')
        ->assertSee('Enable dark theme')
        ->assertSee('Enable system theme');
});
