<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\PodcastIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    expect($data)->toHaveKeys(['podcast', 'seoSource'])
        ->and($data['podcast'])
        ->toBeInstanceOf(Podcast::class)
        ->and($data['podcast']?->is($podcast))
        ->toBeTrue()
        ->and($data['podcast']?->published_episodes_count)
        ->toBe(1)
        ->and($data['seoSource']->title)
        ->toBe('Podcast');
});
