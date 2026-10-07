<?php

use App\Enums\SocialPlatform;
use App\Models\Podcast;
use App\Models\Project;
use App\Models\SocialProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Support\SocialProfileFixtures;

use function Pest\Laravel\from;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('renders the homepage without requesting external statistics on a cold cache', function () {
    Cache::flush();
    Http::fake();

    get(route('home'))
        ->assertOk();

    Http::assertNothingSent();
});

it('presents featured projects without duplicated summaries or invented artwork', function (int $count) {
    foreach (range(1, $count) as $index) {
        Project::factory()->published()
            ->featured()
            ->create([
                'description' => "A distinct project summary {$index}.",
            ]);
    }

    $response = get(route('home'));

    $response->assertOk()
        ->assertSee('Selected work')
        ->assertDontSee('Domain overview')
        ->assertDontSee('home-case-study-fallback');
    $content = $response->getContent();
    if (! is_string($content)) {
        throw new RuntimeException('Expected homepage HTML.');
    }

    expect(substr_count($content, 'data-project-entry'))->toBe($count)
        ->and(substr_count($content, 'A distinct project summary 1.'))
        ->toBe(1);
})->with([1, 2, 4]);

it('omits selected work when no projects are featured', function () {
    get(route('home'))
        ->assertOk()
        ->assertDontSeeHtml('data-home-work');
});

it('uses the active podcast artwork on the homepage', function () {
    Podcast::factory()->create([
        'cover_image_path' => 'podcasts/current-cover.webp',
    ]);

    get(route('home'))
        ->assertOk()
        ->assertSeeHtml('/storage/podcasts/current-cover.webp');
});

it('flashes invalid newsletter input for recovery', function () {
    from(route('home'))
        ->post(route('newsletter.subscribe'), ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email')
        ->assertSessionHasInput('email', 'not-an-email');

});

it('presents honest inquiry links and one media destination per channel', function () {
    get(route('home'))
        ->assertOk()
        ->assertSee('Explore services')
        ->assertDontSee('Book a review')
        ->assertSee('Listen to the podcast')
        ->assertDontSee('Browse the podcast')
        ->assertDontSee('Field notes');
});

it('renders enabled footer social profiles and hides contact-only and disabled ones', function () {
    SocialProfile::query()->delete();

    $footerProfile = SocialProfileFixtures::create(SocialPlatform::GitHub, 'https://github.com/footer-only', true, false);
    $contactProfile = SocialProfileFixtures::create(SocialPlatform::LinkedIn, 'https://linkedin.com/in/contact-only', false, true);
    SocialProfileFixtures::create(SocialPlatform::Bluesky, 'https://bsky.app/profile/disabled', true, true, false);

    get(route('home'))
        ->assertSeeHtml($footerProfile->url)
        ->assertDontSeeHtml($contactProfile->url)
        ->assertDontSeeHtml('https://bsky.app/profile/disabled');
});

it('uses the enabled YouTube profile for the homepage channel link', function () {
    SocialProfile::query()->delete();

    $youtubeProfile = SocialProfileFixtures::create(
        SocialPlatform::YouTube,
        'https://youtube.com/@managed-channel',
        false,
        false,
    );

    get(route('home'))
        ->assertSeeHtml($youtubeProfile->url);

    $youtubeProfile->update(['is_enabled' => false]);

    get(route('home'))
        ->assertDontSeeHtml($youtubeProfile->url);
});
