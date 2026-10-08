<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\ProjectListing;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Tags\Tag;

final readonly class ProjectListingQuery
{
    /**
     * The published projects in display order with their tags, narrowed to a technology (matched
     * case-insensitively) and a topic tag slug, plus the options every published project offers.
     * Projects are few and technologies live in a JSON list that only Project::technologies()
     * normalises, so one load serves both the options and the in-memory filtering.
     */
    public function get(?string $technology = null, ?string $tagSlug = null): ProjectListing
    {
        $publishedProjects = Project::query()
            ->published()
            ->with('tags')
            ->orderBy('sort_order')
            ->get();
        $technologies = $this->technologies($publishedProjects);
        $tags = $this->tags($publishedProjects);

        return new ProjectListing(
            projects: $publishedProjects
                ->filter(fn (Project $project): bool => $this->matches($project, $technology, $tagSlug))
                ->values(),
            technologies: $technologies,
            tags: $tags,
            technology: $technology === null
                ? null
                : array_find($technologies, fn (string $option): bool => strcasecmp($option, $technology) === 0),
            tag: $tagSlug === null
                ? null
                : array_find($tags, fn (Tag $tag): bool => $tag->slug === $tagSlug),
        );
    }

    /**
     * Each technology once, keeping the first spelling found, in natural case-insensitive order.
     *
     * @param  Collection<int, Project>  $projects
     * @return list<non-empty-string>
     */
    private function technologies(Collection $projects): array
    {
        $technologies = [];

        foreach ($projects as $project) {
            array_push($technologies, ...$project->technologies());
        }

        return array_values(collect($technologies)
            ->unique(fn (string $technology): string => mb_strtolower($technology))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->all());
    }

    /**
     * Each tag once, ordered by name.
     *
     * @param  Collection<int, Project>  $projects
     * @return list<Tag>
     */
    private function tags(Collection $projects): array
    {
        $tags = [];

        foreach ($projects as $project) {
            foreach ($project->tags as $tag) {
                if ($tag instanceof Tag) {
                    $tags[] = $tag;
                }
            }
        }

        return array_values(collect($tags)
            ->unique('id')
            ->sortBy(fn (Tag $tag): string => $tag->name)
            ->all());
    }

    private function matches(Project $project, ?string $technology, ?string $tagSlug): bool
    {
        if ($technology !== null && ! array_any(
            $project->technologies(),
            fn (string $projectTechnology): bool => strcasecmp($projectTechnology, $technology) === 0,
        )) {
            return false;
        }

        if ($tagSlug === null) {
            return true;
        }

        return $project->tags->contains(fn (mixed $tag): bool => $tag instanceof Tag && $tag->slug === $tagSlug);
    }
}
