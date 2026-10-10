<?php

use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\EditEpisode;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('publishes an episode through Filament and exposes it publicly', function () {
    $episode = Episode::factory()->create();

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Episode published');

    $episode->refresh();

    expect($episode->status)->toBe(PublishStatus::Published);

    get(route('podcasts.episode', [$episode->podcast, $episode]))
        ->assertOk();
    get('/sitemap.xml')
        ->assertSeeHtml(route('podcasts.episode', [$episode->podcast, $episode]));
});

it('hides an episode again when Filament unpublishes it', function () {
    $episode = Episode::factory()
        ->published()
        ->create();

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified('Episode unpublished');

    $episode->refresh();

    expect($episode->status)->toBe(PublishStatus::Draft);

    get(route('podcasts.episode', [$episode->podcast, $episode]))
        ->assertNotFound();
    get('/sitemap.xml')
        ->assertDontSeeHtml(route('podcasts.episode', [$episode->podcast, $episode]));
});

it('keeps an episode hidden while its published date is scheduled in the future', function () {
    $episode = Episode::factory()->create(['published_at' => now()->addDay()]);

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Episode published');

    $episode->refresh();

    expect($episode->status)->toBe(PublishStatus::Scheduled)
        ->and($episode->published_at)
        ->not->toBeNull()
        ->and(Date::parse($episode->published_at)->isFuture())
        ->toBeTrue();

    get(route('podcasts.episode', [$episode->podcast, $episode]))
        ->assertNotFound();
    get('/sitemap.xml')
        ->assertDontSeeHtml(route('podcasts.episode', [$episode->podcast, $episode]));
});
