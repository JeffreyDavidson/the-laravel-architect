<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Post;
use App\Models\Project;
use App\Presenters\ProjectPresenter;
use App\Queries\RelatedProjectContentQuery;
use App\Queries\RelatedProjectsQuery;
use Illuminate\Database\Eloquent\Collection;
use JeffreyDavidson\CreatorKit\Contracts\PageViewModel;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use JeffreyDavidson\CreatorKit\ViewModels\Concerns\AppliesStoredSeo;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class ProjectShowViewModel implements PageViewModel
{
    use AppliesStoredSeo;

    public function __construct(
        private RelatedProjectsQuery $relatedProjectsQuery,
        private RelatedProjectContentQuery $relatedProjectContentQuery,
        private SiteStructuredData $site,
    ) {}

    /**
     * The project case study, keeping any SEO fields saved in the admin.
     *
     * @return array{
     *     project: Project,
     *     otherProjects: Collection<int, Project>,
     *     relatedPosts: Collection<int, Post>,
     *     relatedEpisodes: Collection<int, Episode>,
     *     pageMeta: PageMeta,
     * }
     */
    public function data(Project $project): array
    {
        $presenter = ProjectPresenter::from($project);

        return [
            ...$this->pageData($project),
            'pageMeta' => new PageMeta(
                seo: $this->withStoredSeo($project, new SEOData(
                    title: $project->title,
                    description: $project->description,
                    image: $presenter->featuredImageUrl(),
                )),
                structuredData: [
                    $presenter->creativeWorkSchema($this->site->authorReference()),
                    $this->site->breadcrumbs([
                        ['name' => 'Projects', 'url' => route('projects.index')],
                        ['name' => $project->title, 'url' => route('projects.show', $project)],
                    ]),
                ],
            ),
        ];
    }

    /**
     * @return array{
     *     project: Project,
     *     otherProjects: Collection<int, Project>,
     *     relatedPosts: Collection<int, Post>,
     *     relatedEpisodes: Collection<int, Episode>,
     *     pageMeta: PageMeta,
     * }
     */
    public function previewData(Project $project): array
    {
        return [
            ...$this->pageData($project),
            'pageMeta' => new PageMeta(new SEOData(
                title: $project->title.' — Preview',
                description: $project->description,
                robots: 'noindex, nofollow',
            )),
        ];
    }

    /**
     * @return array{
     *     project: Project,
     *     otherProjects: Collection<int, Project>,
     *     relatedPosts: Collection<int, Post>,
     *     relatedEpisodes: Collection<int, Episode>,
     * }
     */
    private function pageData(Project $project): array
    {
        $project->load('tags');
        $relatedContent = $this->relatedProjectContentQuery->get($project);

        return [
            'project' => $project,
            'otherProjects' => $this->relatedProjectsQuery->get($project),
            'relatedPosts' => $relatedContent['posts'],
            'relatedEpisodes' => $relatedContent['episodes'],
        ];
    }
}
