<?php

use App\Enums\PublishStatus;
use App\Models\Project;
use App\Models\Tag;
use App\ViewModels\ProjectIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('builds the public project index payload', function () {
    $laterProject = Project::query()->create([
        'title' => 'Later Project',
        'slug' => 'later-project',
        'description' => 'Description',
        'tech_stack' => ['Laravel'],
        'sort_order' => 2,
        'status' => PublishStatus::Published,
    ]);
    $tag = Tag::query()->create([
        'name' => ['en' => 'Laravel'],
        'slug' => ['en' => 'laravel'],
    ]);
    $laterProject->attachTag($tag);
    $earlierProject = Project::query()->create([
        'title' => 'Earlier Project',
        'slug' => 'earlier-project',
        'description' => 'Description',
        'sort_order' => 1,
        'status' => PublishStatus::Published,
    ]);
    Project::query()->create([
        'title' => 'Draft Project',
        'slug' => 'draft-project',
        'description' => 'Description',
        'sort_order' => 0,
        'status' => PublishStatus::Draft,
    ]);
    $data = app(ProjectIndexViewModel::class)
        ->data();

    expect($data)->toHaveKeys(['projects', 'technologyOptions', 'tagOptions', 'seoSource'])
        ->and($data['projects']->modelKeys())
        ->toBe([
            $earlierProject->getKey(),
            $laterProject->getKey(),
        ])
        ->and($data['projects']->every(
            fn (Project $project): bool => $project->relationLoaded('tags'),
        ))->toBeTrue()
        ->and($data['technologyOptions'])
        ->toBe(['Laravel' => 'Laravel'])
        ->and($data['tagOptions'])
        ->toBe(['laravel' => 'Laravel'])
        ->and($data['seoSource']->title)
        ->toBe('Projects');
});
