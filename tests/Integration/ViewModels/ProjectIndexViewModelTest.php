<?php

use App\Models\Project;
use App\Models\Tag;
use App\ViewModels\ProjectIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StructuredDataExpectations as Schema;

pest()->use(RefreshDatabase::class);

it('lists every project on the project index', function () {
    Schema::useFixedOrigin();
    Project::factory()
        ->published()
        ->create(['title' => 'First project', 'slug' => 'first-project', 'sort_order' => 1]);
    Project::factory()
        ->published()
        ->create(['title' => 'Second project', 'slug' => 'second-project', 'sort_order' => 2]);

    $data = app(ProjectIndexViewModel::class)
        ->data();

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        ...Schema::collection('Projects', 'https://example.test/projects', [
            1 => ['First project', 'https://example.test/projects/first-project'],
            2 => ['Second project', 'https://example.test/projects/second-project'],
        ]),
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Projects', 'https://example.test/projects'],
        ]),
    ]);
});

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

    expect($data)->toHaveKeys(['projects', 'technologyOptions', 'tagOptions', 'pageMeta'])
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
        ->and($data['pageMeta']->seo->title)
        ->toBe('Projects');
});
