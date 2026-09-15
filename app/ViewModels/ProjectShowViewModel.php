<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Post;
use App\Models\Project;
use App\Queries\RelatedProjectContentQuery;
use App\Queries\RelatedProjectsQuery;
use Illuminate\Database\Eloquent\Collection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ProjectShowViewModel
{
    public function __construct(
        private readonly RelatedProjectsQuery $relatedProjectsQuery,
        private readonly RelatedProjectContentQuery $relatedProjectContentQuery,
    ) {}

    /**
     * @return array{
     *     project: Project,
     *     otherProjects: Collection<int, Project>,
     *     relatedPosts: Collection<int, Post>,
     *     relatedEpisodes: Collection<int, Episode>,
     *     seoSource: Project,
     * }
     */
    public function data(Project $project): array
    {
        $project->load('tags');
        $relatedContent = $this->relatedProjectContentQuery->get($project);

        return [
            'project' => $project,
            'otherProjects' => $this->relatedProjectsQuery->get($project),
            'relatedPosts' => $relatedContent['posts'],
            'relatedEpisodes' => $relatedContent['episodes'],
            'seoSource' => $project,
        ];
    }

    /**
     * @return array{
     *     project: Project,
     *     otherProjects: Collection<int, Project>,
     *     relatedPosts: Collection<int, Post>,
     *     relatedEpisodes: Collection<int, Episode>,
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
