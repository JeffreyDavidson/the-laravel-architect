<?php

use App\Models\Podcast;
use Database\Seeders\PodcastSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can seed the canonical podcasts repeatedly', function () {
    $this->seed(PodcastSeeder::class);

    Podcast::query()
        ->where('slug', 'coffee-with-the-laravel-architect')
        ->update(['name' => 'Changed podcast name']);

    $this->seed(PodcastSeeder::class);

    expect(Podcast::query()->count())->toBe(2)
        ->and(Podcast::query()->where('slug', 'coffee-with-the-laravel-architect')->value('name'))
        ->toBe('Coffee with The Laravel Architect')
        ->and(Podcast::query()->orderBy('sort_order')->pluck('slug')->all())->toBe([
            'coffee-with-the-laravel-architect',
            'embracing-cloudy-days',
        ]);
});
