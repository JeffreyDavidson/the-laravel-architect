<?php

use App\Models\Project;
use App\Queries\PublishedProjectQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('finds a published project by its slug', function () {
    $project = Project::factory()
        ->published()
        ->create();

    $found = app(PublishedProjectQuery::class)->findBySlug($project->slug);

    expect($found?->is($project))
        ->toBeTrue();
});

it('returns null for a draft project, an unknown slug or a blank slug', function (string $slug) {
    Project::factory()->create(['slug' => 'draft-project']);

    $found = app(PublishedProjectQuery::class)->findBySlug($slug);

    expect($found)
        ->toBeNull();
})->with([
    'draft' => ['draft-project'],
    'unknown' => ['missing-project'],
    'blank' => [''],
]);
