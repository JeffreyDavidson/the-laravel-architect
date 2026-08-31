<?php

use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('backfills responsive variants for existing podcast cover images', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('podcast.png', 1280, 72);
    Storage::disk('public')->put('podcasts/podcast.png', $image->getContent());

    Podcast::withoutEvents(fn () => Podcast::query()->create([
        'name' => 'Podcast',
        'slug' => 'podcast',
        'description' => 'Description',
        'cover_image_path' => 'podcasts/podcast.png',
    ]));

    $this->artisan('podcasts:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 1 podcast.')
        ->assertSuccessful();

    Storage::disk('public')->assertExists([
        'podcasts/responsive/podcast-640.webp',
        'podcasts/responsive/podcast-1280.webp',
    ]);

    $this->artisan('podcasts:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 0 podcasts.')
        ->expectsOutputToContain('Skipped 1 already verified podcast.')
        ->assertSuccessful();

    $this->artisan('podcasts:generate-image-variants', ['--force' => true])
        ->expectsOutputToContain('Generated responsive images for 1 podcast.')
        ->doesntExpectOutputToContain('Skipped')
        ->assertSuccessful();
});
