<?php

use App\Filament\Resources\Podcasts\Pages\EditPodcast;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('controls public podcast visibility through the active toggle', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Visibility workflow podcast',
        'slug' => 'visibility-workflow-podcast',
        'description' => 'Podcast visibility coverage.',
        'is_active' => true,
    ]);

    livewire(EditPodcast::class, ['record' => $podcast->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->get(route('podcast.index'))
        ->assertOk()
        ->assertDontSee($podcast->name);
    $this->get(route('podcast.show', $podcast))->assertNotFound();
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertDontSee(route('podcast.show', $podcast), false);

    livewire(EditPodcast::class, ['record' => $podcast->getRouteKey()])
        ->fillForm(['is_active' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->get(route('podcast.index'))
        ->assertOk()
        ->assertSee($podcast->name);
    $this->get(route('podcast.show', $podcast))
        ->assertOk()
        ->assertSee($podcast->name);
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('podcast.show', $podcast), false);
});
