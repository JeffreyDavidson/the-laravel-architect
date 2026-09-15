<?php

use App\Models\Project;
use App\Models\Tag;
use App\Support\Content\ProjectReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('reports the missing public project details', function () {
    $project = Project::query()->create([
        'title' => 'Incomplete project',
        'slug' => 'incomplete-project',
        'description' => 'A project description.',
    ]);

    $readiness = new ProjectReadiness($project);

    expect($readiness->isReady())->toBeFalse()
        ->and($readiness->label())->toBe('Needs attention')
        ->and($readiness->progress())->toBe('1/6 complete')
        ->and($readiness->missingSummary())->toBe('Missing: Case study, Featured image, Project link, Tech stack, Tags');
});

it('reports a project as ready when all public details are present', function () {
    $project = Project::query()->create([
        'title' => 'Complete project',
        'slug' => 'complete-project',
        'description' => 'A project description.',
        'content' => 'A complete case study.',
        'featured_image_path' => 'projects/complete.webp',
        'url' => 'https://example.com',
        'tech_stack' => ['Laravel', 'Filament'],
    ]);
    $project->attachTag(Tag::query()->create([
        'name' => ['en' => 'Laravel'],
        'slug' => ['en' => 'laravel'],
    ]));

    $readiness = new ProjectReadiness($project->fresh(['tags']));

    expect($readiness->isReady())->toBeTrue()
        ->and($readiness->label())->toBe('Ready')
        ->and($readiness->progress())->toBe('6/6 complete')
        ->and($readiness->missingSummary())->toBe('All public details are complete.');
});
