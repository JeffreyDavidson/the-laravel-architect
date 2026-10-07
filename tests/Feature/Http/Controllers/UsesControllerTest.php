<?php

use function Pest\Laravel\get;

it('renders every uses section in order with its items', function () {
    get(route('uses'))
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
    $content = (string) get(route('uses'))
        ->assertOk()
        ->getContent();

    expect(substr_count($content, "href=\"#{$sectionId}\""))->toBe(2);
})->with(['hardware', 'development', 'content-creation', 'productivity', 'this-site']);
