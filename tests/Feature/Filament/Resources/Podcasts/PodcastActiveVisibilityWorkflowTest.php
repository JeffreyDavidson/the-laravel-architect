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

it('controls public podcast visibility through the active toggle', function () {
    $podcast = Podcast::factory()->create(['name' => 'Visibility workflow podcast']);

    livewire(EditPodcast::class, ['record' => $podcast->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    get(route('podcast.index'))
        ->assertOk()
        ->assertDontSee($podcast->name);
    get(route('podcast.show', $podcast))
        ->assertNotFound();
    get('/sitemap.xml')
        ->assertOk()
        ->assertDontSeeHtml(route('podcast.show', $podcast));

    livewire(EditPodcast::class, ['record' => $podcast->getRouteKey()])
        ->fillForm(['is_active' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    get(route('podcast.index'))
        ->assertOk()
        ->assertSee($podcast->name);
    get(route('podcast.show', $podcast))
        ->assertOk()
        ->assertSee($podcast->name);
    get('/sitemap.xml')
        ->assertOk()
        ->assertSeeHtml(route('podcast.show', $podcast));
});
