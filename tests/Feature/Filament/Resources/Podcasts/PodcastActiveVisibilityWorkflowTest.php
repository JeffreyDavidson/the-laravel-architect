<?php

use App\Filament\Resources\Podcasts\Pages\EditPodcast;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('hides a podcast from the public site when the active toggle is turned off', function () {
    // Arrange
    $podcast = Podcast::factory()->create(['name' => 'Visibility workflow podcast']);

    // Act
    $component = livewire(EditPodcast::class, ['record' => $podcast->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->call('save');

    // Assert
    $component->assertHasNoFormErrors();

    $indexResponse = get(route('podcasts.index'));
    $showResponse = get(route('podcasts.show', $podcast));
    $sitemapResponse = get(route('sitemap'));

    $indexResponse
        ->assertOk()
        ->assertDontSee($podcast->name);
    $showResponse->assertNotFound();
    $sitemapResponse
        ->assertOk()
        ->assertDontSeeHtml(route('podcasts.show', $podcast));
});

it('shows a podcast on the public site when the active toggle is turned on', function () {
    // Arrange
    $podcast = Podcast::factory()
        ->inactive()
        ->create(['name' => 'Visibility workflow podcast']);

    // Act
    $component = livewire(EditPodcast::class, ['record' => $podcast->getRouteKey()])
        ->fillForm(['is_active' => true])
        ->call('save');

    // Assert
    $component->assertHasNoFormErrors();

    $indexResponse = get(route('podcasts.index'));
    $showResponse = get(route('podcasts.show', $podcast));
    $sitemapResponse = get(route('sitemap'));

    $indexResponse
        ->assertOk()
        ->assertSee($podcast->name);
    $showResponse
        ->assertOk()
        ->assertSee($podcast->name);
    $sitemapResponse
        ->assertOk()
        ->assertSeeHtml(route('podcasts.show', $podcast));
});
