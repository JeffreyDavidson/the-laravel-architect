<?php

use App\Models\Podcast;

it('renders nothing for a podcast without cover artwork', function () {
    $podcast = new Podcast([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
    ]);

    $html = (string) $this->blade(
        '<x-podcast-cover :podcast="$podcast" sizes="224px" width="224" height="224" />',
        ['podcast' => $podcast],
    );

    expect(trim($html))->toBeEmpty();
});

it('renders an uploaded podcast cover', function () {
    $podcast = new Podcast([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'cover_image_path' => 'podcasts/cover.png',
    ]);

    $html = (string) $this->blade(
        '<x-podcast-cover :podcast="$podcast" sizes="224px" width="224" height="224" />',
        ['podcast' => $podcast],
    );

    expect($html)->toContain('src="'.$podcast->cover_image_url.'"');
});

it('renders the placeholder for a podcast without cover artwork', function () {
    $podcast = new Podcast([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
    ]);

    $html = (string) $this->blade(
        '<x-podcast-cover :podcast="$podcast" sizes="224px" width="224" height="224"><x-slot:placeholder><span data-cover-placeholder></span></x-slot:placeholder></x-podcast-cover>',
        ['podcast' => $podcast],
    );

    expect(trim($html))->toBe('<span data-cover-placeholder></span>');
});

it('leaves out the placeholder when the podcast has cover artwork', function () {
    $podcast = new Podcast([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'cover_image_path' => 'podcasts/cover.png',
    ]);

    $html = (string) $this->blade(
        '<x-podcast-cover :podcast="$podcast" sizes="224px" width="224" height="224"><x-slot:placeholder><span data-cover-placeholder></span></x-slot:placeholder></x-podcast-cover>',
        ['podcast' => $podcast],
    );

    expect($html)->toContain('<picture>')
        ->not->toContain('data-cover-placeholder');
});
