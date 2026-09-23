<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('prioritizes main destinations and keeps secondary links discoverable', function (): void {
    $response = $this->get(route('home'));
    $response->assertOk();

    $content = $response->getContent();

    if (! is_string($content)) {
        throw new RuntimeException('The home page response did not contain HTML.');
    }

    $mainPosition = strpos($content, '<main');

    if (! is_int($mainPosition)) {
        throw new RuntimeException('The home page response did not contain a main landmark.');
    }

    $header = substr($content, 0, $mainPosition);

    foreach (['Services', 'Projects', 'Writing', 'About', 'Search', 'Archive'] as $label) {
        expect(str_contains($header, ">{$label}<"))->toBeTrue();
    }

    foreach (['Podcast', 'Uses'] as $label) {
        expect(str_contains($header, ">{$label}<"))->toBeFalse();
        expect(str_contains($content, ">{$label}<"))->toBeTrue();
    }

    $aboutPosition = strpos($header, '>About<');
    $searchPosition = strpos($header, '>Search<');
    $archivePosition = strpos($header, '>Archive<');

    if (! is_int($aboutPosition) || ! is_int($searchPosition) || ! is_int($archivePosition)) {
        throw new RuntimeException('The home page header did not contain its primary and utility links.');
    }

    expect($aboutPosition)->toBeLessThan($searchPosition);
    expect($searchPosition)->toBeLessThan($archivePosition);
    expect(str_contains($content, 'Discuss a Project'))->toBeTrue();
});
