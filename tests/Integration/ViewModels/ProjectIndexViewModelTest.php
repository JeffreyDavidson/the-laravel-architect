<?php

use App\Models\Project;
use App\Models\Tag;
use App\ViewModels\ProjectIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('builds the public project index payload', function () {
    $laterProject = Project::factory()
        ->published()
        ->create([
            'tech_stack' => ['Laravel'],
            'sort_order' => 2,
        ]);
    $tag = Tag::factory()->create(['name' => 'Laravel']);
    $laterProject->attachTag($tag);
    $earlierProject = Project::factory()
        ->published()
        ->create(['sort_order' => 1]);
    Project::factory()->create();
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
