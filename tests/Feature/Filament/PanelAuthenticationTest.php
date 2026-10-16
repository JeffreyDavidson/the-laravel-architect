<?php

use App\Filament\Pages\Dashboard;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\withVite;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../Support/PublicPageAssertions.php';

it('redirects a guest from the admin panel to its login page', function () {
    // Act
    $response = get(Dashboard::getUrl());

    // Assert
    $response->assertRedirect(Filament::getPanel('admin')->getLoginUrl());
});

it('renders the admin login page with the panel theme, favicon and appearance controls', function () {
    // Arrange
    withVite();

    $manifest = assetManifest();

    // Act
    $response = get(Filament::getPanel('admin')->getLoginUrl() ?? '');

    // Assert
    $response
        ->assertOk()
        ->assertSeeHtml($manifest['resources/css/filament/admin/theme.css']['file'])
        ->assertSeeHtml('<link rel="icon" href="/images/elephant-small-32.png" />')
        ->assertSee('Appearance')
        ->assertSee('Enable light theme')
        ->assertSee('Enable dark theme')
        ->assertSee('Enable system theme');
});
