<?php

use App\Models\Project;
use App\ViewModels\ProjectShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('builds the project detail payload', function () {
    $project = Project::factory()
        ->published()
        ->create(['sort_order' => 1]);
    $relatedProject = Project::factory()
        ->published()
        ->create(['sort_order' => 2]);

    $data = app(ProjectShowViewModel::class)
        ->data($project);

    expect($data)->toHaveKeys(['project', 'otherProjects', 'seoSource'])
        ->and($data['project']->is($project))
        ->toBeTrue()
        ->and($data['project']->relationLoaded('tags'))
        ->toBeTrue()
        ->and($data['otherProjects']->modelKeys())
        ->toBe([$relatedProject->getKey()])
        ->and($data['seoSource']->is($project))
        ->toBeTrue();
});
