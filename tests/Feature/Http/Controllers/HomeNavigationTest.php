<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('prioritizes main destinations and keeps secondary links discoverable', function (): void {
    $response = $this->get(route('home'));
    $response->assertOk();

    $content = $response->getContent();

    expect($content)->toBeString();

    $mainPosition = strpos($content, '<main');

    expect($mainPosition)->toBeInt();

    $header = substr($content, 0, $mainPosition);

    foreach (['Services', 'Projects', 'Writing', 'About', 'Search', 'Archive'] as $label) {
        expect(str_contains($header, ">{$label}<"))->toBeTrue();
    }

    foreach (['Podcast', 'Uses'] as $label) {
        expect(str_contains($header, ">{$label}<"))->toBeFalse();
        expect(str_contains($content, ">{$label}<"))->toBeTrue();
    }

    expect(strpos($header, '>About<'))->toBeLessThan(strpos($header, '>Search<'));
    expect(strpos($header, '>Search<'))->toBeLessThan(strpos($header, '>Archive<'));
    expect(str_contains($content, 'Discuss a Project'))->toBeTrue();
});
