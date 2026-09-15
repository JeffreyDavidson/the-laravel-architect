<?php

namespace App\ViewModels;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Tags\Tag;

class ProjectIndexViewModel
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     projects: EloquentCollection<int, Project>,
     *     technologyOptions: array<string, non-empty-string>,
     *     tagOptions: array<string, string>,
     *     selectedTechnology: string|null,
     *     selectedTag: string|null,
     *     hasFilters: bool,
     *     seoSource: SEOData,
     * }
     */
    public function data(array $filters = []): array
    {
        $selectedTechnology = $this->normaliseFilter($filters['technology'] ?? null);
        $selectedTag = $this->normaliseFilter($filters['tag'] ?? null);
        $allProjects = Project::published()
            ->with('tags')
            ->orderBy('sort_order')
            ->get();
        $technologies = $this->technologies($allProjects);
        $tags = $this->tags($allProjects);

        if ($selectedTechnology !== null) {
            $technologyOption = $technologies->first(
                fn (string $technology): bool => strcasecmp($technology, $selectedTechnology) === 0,
            );
            $selectedTechnology = is_string($technologyOption) ? $technologyOption : null;
        }

        if ($selectedTag !== null) {
            $tagOption = $tags->first(
                fn (Tag $tag): bool => $tag->slug === $selectedTag,
            );
            $selectedTag = $tagOption instanceof Tag ? $tagOption->slug : null;
        }

        $projects = $allProjects;

        if ($selectedTechnology !== null) {
            $projects = $projects->filter(
                fn (Project $project): bool => $this->matchesTechnology($project, $selectedTechnology),
            )->values();
        }

        if ($selectedTag !== null) {
            $projects = $projects->filter(
                fn (Project $project): bool => $this->matchesTag($project, $selectedTag),
            )->values();
        }

        return [
            'projects' => $projects,
            'technologyOptions' => $technologies
                ->mapWithKeys(fn (string $technology): array => [$technology => $technology])
                ->all(),
            'tagOptions' => $tags
                ->mapWithKeys(fn (Tag $tag): array => [$tag->slug => $tag->name])
                ->all(),
            'selectedTechnology' => $selectedTechnology,
            'selectedTag' => $selectedTag,
            'hasFilters' => $selectedTechnology !== null || $selectedTag !== null,
            'seoSource' => new SEOData(
                title: 'Projects',
                description: 'Explore the products I’ve built, the problems they solve, and the work behind them.',
            ),
        ];
    }

    private function normaliseFilter(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param  EloquentCollection<int, Project>  $projects
     * @return Collection<int, non-empty-string>
     */
    private function technologies(EloquentCollection $projects): Collection
    {
        $technologies = [];

        foreach ($projects as $project) {
            $techStack = $project->tech_stack;

            if (! is_array($techStack)) {
                continue;
            }

            foreach ($techStack as $technology) {
                if (is_string($technology) && trim($technology) !== '') {
                    $technologies[] = trim($technology);
                }
            }
        }

        return collect($technologies)
            ->unique(fn (string $technology): string => mb_strtolower($technology))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * @param  EloquentCollection<int, Project>  $projects
     * @return Collection<int, Tag>
     */
    private function tags(EloquentCollection $projects): Collection
    {
        $tags = [];

        foreach ($projects as $project) {
            foreach ($project->tags as $tag) {
                if ($tag instanceof Tag) {
                    $tags[] = $tag;
                }
            }
        }

        return collect($tags)
            ->unique('id')
            ->sortBy(fn (Tag $tag): string => $tag->name)
            ->values();
    }

    private function matchesTechnology(Project $project, string $technology): bool
    {
        $techStack = $project->tech_stack;

        if (! is_array($techStack)) {
            return false;
        }

        return array_any(
            $techStack,
            fn (mixed $projectTechnology): bool => is_string($projectTechnology)
                && strcasecmp(trim($projectTechnology), $technology) === 0,
        );
    }

    private function matchesTag(Project $project, string $slug): bool
    {
        foreach ($project->tags as $tag) {
            if ($tag instanceof Tag && $tag->slug === $slug) {
                return true;
            }
        }

        return false;
    }
}
