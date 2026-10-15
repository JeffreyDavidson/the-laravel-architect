<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../../Support/PublicPageAssertions.php';

it('keeps public technology and channel details consistent on uses', function () {
    $response = get(route('uses'));
    $response->assertOk()
        ->assertSee('Laravel '.configuredString(config('public-site.technology.laravel')))
        ->assertSee('Filament '.configuredString(config('public-site.technology.filament')));
});

it('places the mobile uses jump navigation before the equipment list', function () {
    $response = get(route('uses'));

    $response
        ->assertOk()
        ->assertSeeHtml('aria-label="Jump to uses section"');

    $content = responseContent($response->getContent());

    expect(stringPosition($content, 'aria-label="Jump to uses section"'))
        ->toBeLessThan(stringPosition($content, 'id="hardware"'));
});

it('renders every uses section in order with its items', function () {
    $response = get(route('uses'));

    $response
        ->assertOk()
        ->assertViewIs('pages.uses')
        ->assertSeeHtmlInOrder([
            '<section id="hardware"',
            'MacBook Pro 16&quot; (Nov 2024)',
            '<section id="development"',
            'Visual Studio Code',
            '<section id="content-creation"',
            'Shure SM7B',
            '<section id="productivity"',
            'Notion',
            '<section id="this-site"',
            'Tailwind CSS',
        ])
        ->assertSeeInOrder(['Hardware', 'Development', 'Content Creation', 'Productivity', 'This Site Is Built With']);
});

it('links to every uses section from the mobile and sidebar navigation', function (string $sectionId) {
    $response = get(route('uses'));

    $response
        ->assertOk();

    $content = (string) $response->getContent();

    expect(substr_count($content, "href=\"#{$sectionId}\""))->toBe(2);
})->with(['hardware', 'development', 'content-creation', 'productivity', 'this-site']);
