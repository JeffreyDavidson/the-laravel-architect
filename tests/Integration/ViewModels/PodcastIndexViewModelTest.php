<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\PodcastIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RalphJSmit\Laravel\SEO\Support\SEOData;

uses(RefreshDatabase::class);

it('builds the public podcast index payload', function () {
    Podcast::query()->create([
        'name' => 'Later Podcast',
        'slug' => 'later-podcast',
        'description' => 'Description',
        'is_active' => true,
        'sort_order' => 2,
    ]);
    $podcast = Podcast::query()->create([
        'name' => 'Earlier Podcast',
        'slug' => 'earlier-podcast',
        'description' => 'Description',
        'is_active' => true,
        'sort_order' => 1,
    ]);
    Podcast::query()->create([
        'name' => 'Inactive Podcast',
        'slug' => 'inactive-podcast',
        'description' => 'Description',
        'is_active' => false,
        'sort_order' => 0,
    ]);
    Episode::query()->create([
        'podcast_id' => $podcast->getKey(),
        'title' => 'Published Episode',
        'slug' => 'published-episode',
        'episode_number' => 1,
        'description' => 'Description',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $data = app(PodcastIndexViewModel::class)
        ->data();

    expect($data)->toHaveKeys(['podcast', 'seoSource'])
        ->and($data['podcast'])->toBeInstanceOf(Podcast::class)
        ->and($data['podcast']->is($podcast))->toBeTrue()
        ->and($data['podcast']->published_episodes_count)->toBe(1)
        ->and($data['seoSource'])->toBeInstanceOf(SEOData::class);
});
