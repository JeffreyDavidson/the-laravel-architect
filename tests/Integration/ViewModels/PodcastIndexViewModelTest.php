<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\PodcastIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StructuredDataExpectations as Schema;

pest()->use(RefreshDatabase::class);

it('builds the public podcast index payload', function () {
    Podcast::factory()->create(['sort_order' => 2]);
    $podcast = Podcast::factory()->create(['sort_order' => 1]);
    Podcast::factory()
        ->inactive()
        ->create();
    Episode::factory()
        ->for($podcast)
        ->published()
        ->create();

    $data = app(PodcastIndexViewModel::class)
        ->data();

    expect($data)->toHaveKeys(['podcast', 'pageMeta'])
        ->and($data['podcast'])
        ->toBeInstanceOf(Podcast::class)
        ->and($data['podcast']?->is($podcast))
        ->toBeTrue()
        ->and($data['podcast']?->published_episodes_count)
        ->toBe(1)
        ->and($data['pageMeta']->seo->title)
        ->toBe('Podcast');
});

it('lists the active podcast on the podcast index', function () {
    Schema::useFixedOrigin();
    Podcast::factory()->create(['name' => 'Coffee Chat', 'slug' => 'coffee-chat']);

    $data = app(PodcastIndexViewModel::class)
        ->data();

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        ...Schema::collection('Podcast', 'https://example.test/podcasts', [
            1 => ['Coffee Chat', 'https://example.test/podcasts/coffee-chat'],
        ]),
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Podcast', 'https://example.test/podcasts'],
        ]),
    ]);
});

it('keeps an empty podcast index listing when no podcast is active', function () {
    Schema::useFixedOrigin();

    $data = app(PodcastIndexViewModel::class)
        ->data();

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        ...Schema::collection('Podcast', 'https://example.test/podcasts', []),
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Podcast', 'https://example.test/podcasts'],
        ]),
    ]);
});
