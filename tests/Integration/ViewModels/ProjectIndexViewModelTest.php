<?php

use App\Models\Project;
use App\Models\Tag;
use App\ViewModels\ProjectIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

it('selects the offered filter options for the project index', function () {
    $tag = Tag::factory()->create(['name' => 'Laravel']);
    $project = Project::factory()
        ->published()
        ->create(['tech_stack' => ['Laravel']]);
    $project->attachTag($tag);

    $data = app(ProjectIndexViewModel::class)
        ->data('laravel', 'laravel');

    expect($data['projects']->modelKeys())->toBe([$project->getKey()])
        ->and($data['selectedTechnology'])
        ->toBe('Laravel')
        ->and($data['selectedTag'])
        ->toBe('laravel')
        ->and($data['hasFilters'])
        ->toBeTrue();
});

it('treats a project filter that no published project offers as not found', function (?string $technology, ?string $tag) {
    Project::factory()
        ->published()
        ->create(['tech_stack' => ['Laravel']])
        ->attachTag(Tag::factory()->create(['name' => 'Laravel']));

    app(ProjectIndexViewModel::class)
        ->data($technology, $tag);
})->throws(NotFoundHttpException::class)
    ->with([
        'unknown technology' => ['Rust', null],
        'unknown topic' => [null, 'rust'],
    ]);
