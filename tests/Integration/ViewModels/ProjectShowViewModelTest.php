<?php

use App\Models\Project;
use App\ViewModels\ProjectShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StructuredDataExpectations as Schema;

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

    expect($data)->toHaveKeys(['project', 'otherProjects', 'pageMeta'])
        ->and($data['project']->is($project))
        ->toBeTrue()
        ->and($data['project']->relationLoaded('tags'))
        ->toBeTrue()
        ->and($data['otherProjects']->modelKeys())
        ->toBe([$relatedProject->getKey()])
        ->and($data['pageMeta']->seo->title)
        ->toBe($project->title)
        ->and($data['pageMeta']->seo->description)
        ->toBe($project->description);
});

it('fills the SEO fields the page leaves empty from the ones saved in the admin', function () {
    $project = Project::factory()
        ->published()
        ->create(['title' => 'Ringside', 'description' => 'Wrestling promotion software.']);
    $project->seo()
        ->update([
            'title' => 'Saved title',
            'description' => 'Saved description.',
            'image' => 'https://images.test/saved.png',
            'robots' => 'noindex',
            'canonical_url' => 'https://canonical.test/ringside',
        ]);

    $data = app(ProjectShowViewModel::class)
        ->data($project->refresh());
    $seo = $data['pageMeta']->seo;

    expect($seo->title)->toBe('Ringside')
        ->and($seo->description)
        ->toBe('Wrestling promotion software.')
        ->and($seo->image)
        ->toBe('https://images.test/saved.png')
        ->and($seo->robots)
        ->toBe('noindex')
        ->and($seo->canonical_url)
        ->toBe('https://canonical.test/ringside');
});

it('describes a project case study with its technologies and link', function () {
    Schema::useFixedOrigin();
    $project = Project::factory()
        ->published()
        ->create([
            'title' => 'Ringside',
            'slug' => 'ringside',
            'description' => 'Wrestling promotion software.',
            'tech_stack' => ['Laravel', 'Livewire'],
            'url' => 'https://ringside.test',
        ]);

    $pageMeta = app(ProjectShowViewModel::class)
        ->data($project)['pageMeta'];

    expect(Schema::graph($pageMeta))->toBe([
        Schema::website(),
        [
            '@type' => 'CreativeWork',
            '@id' => 'https://example.test/projects/ringside#project',
            'name' => 'Ringside',
            'url' => 'https://example.test/projects/ringside',
            'mainEntityOfPage' => 'https://example.test/projects/ringside',
            'description' => 'Wrestling promotion software.',
            'author' => Schema::author(),
            'keywords' => 'Laravel, Livewire',
            'sameAs' => ['https://ringside.test'],
        ],
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Projects', 'https://example.test/projects'],
            ['Ringside', 'https://example.test/projects/ringside'],
        ]),
    ]);
});

it('keeps a project preview out of search results without a case study', function () {
    $project = Project::factory()->create(['title' => 'Draft project']);

    $pageMeta = app(ProjectShowViewModel::class)
        ->previewData($project)['pageMeta'];

    expect($pageMeta->seo->title)->toBe('Draft project — Preview')
        ->and($pageMeta->seo->robots)
        ->toBe('noindex, nofollow')
        ->and($pageMeta->structuredData)
        ->toBeEmpty();
});
