<?php

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\ViewModels\ProjectIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RalphJSmit\Laravel\SEO\Support\SEOData;

uses(RefreshDatabase::class);

it('builds the public project index payload', function () {
    $laterProject = Project::query()->create([
        'title' => 'Later Project',
        'slug' => 'later-project',
        'description' => 'Description',
        'sort_order' => 2,
        'status' => ProjectStatus::Published,
    ]);
    $earlierProject = Project::query()->create([
        'title' => 'Earlier Project',
        'slug' => 'earlier-project',
        'description' => 'Description',
        'sort_order' => 1,
        'status' => ProjectStatus::Published,
    ]);
    Project::query()->create([
        'title' => 'Draft Project',
        'slug' => 'draft-project',
        'description' => 'Description',
        'sort_order' => 0,
        'status' => ProjectStatus::Draft,
    ]);

    $data = app(ProjectIndexViewModel::class)
        ->data();

    expect($data)->toHaveKeys(['projects', 'seoSource'])
        ->and($data['projects']->modelKeys())->toBe([
            $earlierProject->getKey(),
            $laterProject->getKey(),
        ])
        ->and($data['projects']->every(
            fn (Project $project): bool => $project->relationLoaded('tags'),
        ))->toBeTrue()
        ->and($data['seoSource'])->toBeInstanceOf(SEOData::class);
});
