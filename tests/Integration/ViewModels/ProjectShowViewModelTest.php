<?php

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\ViewModels\ProjectShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds the project detail payload', function () {
    $project = Project::query()->create([
        'title' => 'Current Project',
        'slug' => 'current-project',
        'description' => 'The current project.',
        'status' => ProjectStatus::Published,
        'sort_order' => 1,
    ]);
    $relatedProject = Project::query()->create([
        'title' => 'Related Project',
        'slug' => 'related-project',
        'description' => 'A related project.',
        'status' => ProjectStatus::Published,
        'sort_order' => 2,
    ]);

    $data = app(ProjectShowViewModel::class)
        ->data($project);

    expect($data)->toHaveKeys(['project', 'otherProjects', 'seoSource'])
        ->and($data['project']->is($project))->toBeTrue()
        ->and($data['project']->relationLoaded('tags'))->toBeTrue()
        ->and($data['otherProjects']->modelKeys())->toBe([$relatedProject->getKey()])
        ->and($data['seoSource']->is($project))->toBeTrue();
});
