<?php

namespace App\ViewModels;

use App\Models\Project;
use App\Queries\RelatedProjectsQuery;
use Illuminate\Database\Eloquent\Collection;

class ProjectShowViewModel
{
    public function __construct(
        private readonly RelatedProjectsQuery $relatedProjectsQuery,
    ) {}

    /**
     * @return array{
     *     project: Project,
     *     otherProjects: Collection<int, Project>,
     *     seoSource: Project,
     * }
     */
    public function data(Project $project): array
    {
        $project->load('tags');

        return [
            'project' => $project,
            'otherProjects' => $this->relatedProjectsQuery->get($project),
            'seoSource' => $project,
        ];
    }
}
