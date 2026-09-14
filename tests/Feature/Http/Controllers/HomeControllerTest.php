<?php

use App\Enums\PublishStatus;
use App\Models\Podcast;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

pest()->use(RefreshDatabase::class);

it('renders the homepage without requesting external statistics on a cold cache', function () {
    Cache::flush();
    Http::fake();

    $this->get(route('home'))
        ->assertOk();

    Http::assertNothingSent();
});

it('presents featured projects without duplicated summaries or invented artwork', function (int $count) {
    foreach (range(1, $count) as $index) {
        Project::query()->create([
            'title' => "Selected project {$index}",
            'description' => "A distinct project summary {$index}.",
            'status' => PublishStatus::Published,
            'is_featured' => true,
            'sort_order' => $index,
        ]);
    }

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Selected work')
        ->assertDontSee('Domain overview')
        ->assertDontSee('home-case-study-fallback');
    $content = $response->getContent();
    if (! is_string($content)) {
        throw new RuntimeException('Expected homepage HTML.');
    }

    expect(substr_count($content, 'data-project-entry'))->toBe($count)
        ->and(substr_count($content, 'A distinct project summary 1.'))->toBe(1);
})->with([1, 2, 4]);

it('omits selected work when no projects are featured', function () {
    $this->get(route('home'))->assertOk()->assertDontSeeHtml('data-home-work');
});

it('uses the active podcast artwork on the homepage', function () {
    Podcast::query()->create([
        'name' => 'Coffee with The Laravel Architect',
        'slug' => 'coffee-with-the-laravel-architect',
        'description' => 'Conversations about Laravel and the developer life.',
        'cover_image_path' => 'podcasts/current-cover.webp',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->get(route('home'))->assertOk()->assertSeeHtml('/storage/podcasts/current-cover.webp');
});

it('flashes invalid newsletter input for recovery', function () {
    $this->from(route('home'))
        ->post(route('newsletter.subscribe'), ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email')
        ->assertSessionHasInput('email', 'not-an-email');

});

it('presents honest inquiry links and one media destination per channel', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Explore services')
        ->assertDontSee('Book a review')
        ->assertSee('Listen to the podcast')
        ->assertDontSee('Browse the podcast')
        ->assertDontSee('Field notes');
});
