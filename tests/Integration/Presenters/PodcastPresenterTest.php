<?php

use App\Models\Podcast;
use App\Presenters\PodcastPresenter;
use App\Services\ResponsiveImageVariants;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;

use function Pest\Laravel\withVite;

covers(PodcastPresenter::class);

it('uses the uploaded cover with its responsive variants', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('cover.png', 1280, 1280);
    Storage::disk('public')->put('podcasts/cover.png', $image->getContent());
    app(ResponsiveImageVariants::class)->generate('podcasts/cover.png');
    $podcast = new Podcast([
        'slug' => 'coffee-with-the-laravel-architect',
        'cover_image_path' => 'podcasts/cover.png',
    ]);

    $cover = PodcastPresenter::from($podcast)->cover();

    expect($cover?->src)->toBe(Storage::disk('public')->url('podcasts/cover.png'))
        ->and($cover?->srcset)
        ->toBe(app(ResponsiveImageVariants::class)->srcset('podcasts/cover.png'))
        ->toContain('cover-640.webp 640w');
});

it('leaves out the srcset for an uploaded cover without variants', function () {
    Storage::fake('public');
    $podcast = new Podcast([
        'slug' => 'coffee-with-the-laravel-architect',
        'cover_image_path' => 'podcasts/cover.png',
    ]);

    $cover = PodcastPresenter::from($podcast)->cover();

    expect($cover?->src)->toBe(Storage::disk('public')->url('podcasts/cover.png'))
        ->and($cover?->srcset)
        ->toBeNull();
});

it('uses bundled artwork for a known podcast without an upload', function (string $slug, string $prefix) {
    withVite();
    $podcast = new Podcast(['slug' => $slug]);

    $cover = PodcastPresenter::from($podcast)->cover();

    expect($cover?->src)->toBe(Vite::asset("resources/images/{$prefix}-512.webp"))
        ->and($cover?->srcset)
        ->toBe(implode(', ', [
            Vite::asset("resources/images/{$prefix}-128.webp").' 128w',
            Vite::asset("resources/images/{$prefix}-320.webp").' 320w',
            Vite::asset("resources/images/{$prefix}-512.webp").' 512w',
        ]));
})->with([
    ['coffee-with-the-laravel-architect', 'podcast-coffee-logo'],
    ['embracing-cloudy-days', 'podcast-cloudy-logo'],
]);

it('has no cover for an unknown podcast without an upload', function () {
    $presenter = PodcastPresenter::from(new Podcast(['slug' => 'architecture-sessions']));

    expect($presenter->cover())->toBeNull()
        ->and($presenter->coverImageUrl())
        ->toBeNull();
});

it('uses valid six-digit hex colors for public presentation', function () {
    $podcast = new Podcast(['color' => '#2A6FDB']);

    expect(PodcastPresenter::from($podcast)->displayColor())->toBe('#2A6FDB');
});

it('falls back to the default public color for invalid values', function (mixed $color) {
    $podcast = new Podcast(['color' => $color]);

    expect(PodcastPresenter::from($podcast)->displayColor())->toBe('#6366f1');
})->with([
    'missing' => null,
    'short hex' => '#fff',
    'non-hex value' => 'rebeccapurple',
    'css expression' => 'url(https://example.com/image.png)',
]);
