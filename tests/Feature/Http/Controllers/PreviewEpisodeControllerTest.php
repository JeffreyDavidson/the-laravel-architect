<?php

use App\Models\Episode;
use App\Presenters\EpisodePresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('renders a signed preview for an unpublished episode', function () {
    $draft = Episode::factory()->create();
    $url = EpisodePresenter::from($draft)->previewUrl();

    $response = get($url);

    $response->assertOk()
        ->assertSee($draft->title)
        ->assertSee('Draft preview');
});
