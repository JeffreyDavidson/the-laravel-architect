<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Project;
use App\Queries\ProjectListingQuery;
use Illuminate\Database\Eloquent\Collection;
use JeffreyDavidson\CreatorKit\Contracts\PageViewModel;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use JeffreyDavidson\CreatorKit\Support\Seo\CollectionListing;
use JeffreyDavidson\CreatorKit\Support\Seo\JsonLd;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Tags\Tag;

final readonly class ProjectIndexViewModel implements PageViewModel
{
    public function __construct(
        private ProjectListingQuery $projectListingQuery,
        private SiteStructuredData $site,
    ) {}

    /**
     * The published projects for the validated technology and topic filters, also split into the
     * featured group and the rest. A filter value that no published project offers is a 404.
     *
     * @return array{
     *     projects: Collection<int, Project>,
     *     featuredProjects: Collection<int, Project>,
     *     otherProjects: Collection<int, Project>,
     *     technologyOptions: array<string, non-empty-string>,
     *     tagOptions: array<string, string>,
     *     selectedTechnology: string|null,
     *     selectedTag: string|null,
     *     hasFilters: bool,
     *     pageMeta: PageMeta,
     * }
     */
    public function data(?string $technology = null, ?string $tag = null): array
    {
        $listing = $this->projectListingQuery->get($technology, $tag);

        abort_if($technology !== null && $listing->technology === null, 404);
        abort_if($tag !== null && ! $listing->tag instanceof Tag, 404);

        $technologyOptions = [];

        foreach ($listing->technologies as $option) {
            $technologyOptions[$option] = $option;
        }

        $tagOptions = [];

        foreach ($listing->tags as $option) {
            $tagOptions[$option->slug] = $option->name;
        }

        return [
            'projects' => $listing->projects,
            'featuredProjects' => $listing->projects->filter(static fn (Project $project): bool => $project->isFeatured()),
            'otherProjects' => $listing->projects->reject(static fn (Project $project): bool => $project->isFeatured()),
            'technologyOptions' => $technologyOptions,
            'tagOptions' => $tagOptions,
            'selectedTechnology' => $listing->technology,
            'selectedTag' => $listing->tag?->slug,
            'hasFilters' => $technology !== null || $tag !== null,
            'pageMeta' => new PageMeta(
                seo: new SEOData(
                    title: 'Projects',
                    description: 'Explore the products I’ve built, the problems they solve, and the work behind them.',
                ),
                structuredData: $this->structuredData($listing->projects),
            ),
        ];
    }

    /**
     * The listed projects as a collection, under the unfiltered projects URL.
     *
     * @param  Collection<int, Project>  $projects
     * @return list<array<string, mixed>>
     */
    private function structuredData(Collection $projects): array
    {
        $url = route('projects.index');

        return [
            ...JsonLd::collectionPage(new CollectionListing(
                'Projects',
                $url,
                array_values(array_map(
                    static fn (Project $project): array => ['name' => $project->title, 'url' => route('projects.show', $project)],
                    $projects->all(),
                )),
            )),
            $this->site->breadcrumbs([['name' => 'Projects', 'url' => $url]]),
        ];
    }
}
