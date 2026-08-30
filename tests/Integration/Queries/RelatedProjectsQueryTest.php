<?php

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Queries\RelatedProjectsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('selects published projects in sort order while excluding the current project', function () {
    $currentProject = createRelatedProjectsQueryProject(
        title: 'Current Project',
        sortOrder: 1,
    );
    $firstRelatedProject = createRelatedProjectsQueryProject(
        title: 'First Related Project',
        sortOrder: 2,
    );
    $secondRelatedProject = createRelatedProjectsQueryProject(
        title: 'Second Related Project',
        sortOrder: 3,
    );
    createRelatedProjectsQueryProject(
        title: 'Draft Project',
        sortOrder: 0,
        status: ProjectStatus::Draft,
    );
    createRelatedProjectsQueryProject(
        title: 'Excluded By Limit',
        sortOrder: 4,
    );

    $relatedProjects = app(RelatedProjectsQuery::class)
        ->get($currentProject, limit: 2);

    expect($relatedProjects->modelKeys())->toBe([
        $firstRelatedProject->getKey(),
        $secondRelatedProject->getKey(),
    ]);
});

function createRelatedProjectsQueryProject(
    string $title,
    int $sortOrder,
    ProjectStatus $status = ProjectStatus::Published,
): Project {
    return Project::query()->create([
        'title' => $title,
        'slug' => str($title)->slug(),
        'description' => "{$title} description.",
        'status' => $status,
        'sort_order' => $sortOrder,
    ]);
}
