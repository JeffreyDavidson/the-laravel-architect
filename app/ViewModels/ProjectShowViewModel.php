<?php

namespace App\ViewModels;

use App\Models\Project;
use App\Queries\RelatedProjectsQuery;
use Illuminate\Database\Eloquent\Collection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

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

    /**
     * @return array{
     *     project: Project,
     *     otherProjects: Collection<int, Project>,
     *     seoSource: SEOData,
     * }
     */
    public function previewData(Project $project): array
    {
        $data = $this->data($project);
        $data['seoSource'] = new SEOData(
            title: $project->title.' — Preview',
            description: $project->description,
            robots: 'noindex, nofollow',
        );

        return $data;
    }
}
