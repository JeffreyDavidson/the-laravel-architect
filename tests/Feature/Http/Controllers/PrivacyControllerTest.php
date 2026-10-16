<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('renders the privacy notice with its own SEO metadata', function () {
    // Act
    $response = get(route('privacy'));

    // Assert
    $response->assertOk()
        ->assertSeeHtml('<title>Privacy — Jeffrey Davidson</title>')
        ->assertSeeHtml('<meta name="description" content="How The Laravel Architect handles contact messages, newsletter subscriptions, observability, and essential site data.">')
        ->assertSeeText('A clear view of your privacy')
        ->assertSeeText('Last updated October 2, 2026');
});

it('labels each privacy section by its heading for assistive technology', function (string $section) {
    // Act
    $response = get(route('privacy'));

    // Assert
    $response->assertOk()
        ->assertSeeHtml("aria-labelledby=\"{$section}\"")
        ->assertSeeHtml("id=\"{$section}\"");
})->with([
    'overview' => ['privacy-overview'],
    'information collected' => ['privacy-information'],
    'automatic data' => ['privacy-automatic'],
    'how it is used' => ['privacy-use'],
    'retention' => ['privacy-retention'],
    'updates' => ['privacy-updates'],
]);
