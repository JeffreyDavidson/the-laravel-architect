<?php

use App\Models\Project;
use App\Presenters\ProjectPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('renders a signed preview for an unpublished project', function () {
    $draft = Project::factory()->create();
    $url = ProjectPresenter::from($draft)->previewUrl();

    $response = get($url);

    $response->assertOk()
        ->assertSee($draft->title);
});
