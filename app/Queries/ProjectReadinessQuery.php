<?php

namespace App\Queries;

use App\Enums\ProjectReadinessFilter;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;

final class ProjectReadinessQuery
{
    /** @param Builder<Project> $query */
    public function apply(Builder $query, ?string $filter): void
    {
        $filled = $this->filledSql(...);
        $techStack = "exists (select 1 from json_each(coalesce(tech_stack, '[]')) where type = 'text' and trim(value, char(32, 9, 10, 11, 13, 0)) <> '')";
        $details = "({$filled('description')}) and ({$filled('url')} or {$filled('github_url')}) and ({$techStack})";

        match (ProjectReadinessFilter::tryFrom((string) $filter)) {
            ProjectReadinessFilter::Ready => $query->whereRaw($filled('featured_image_path'))
                ->whereRaw($filled('content'))
                ->whereRaw($details)
                ->whereHas('tags'),
            ProjectReadinessFilter::NeedsImage => $query->whereRaw('not ('.$filled('featured_image_path').')'),
            ProjectReadinessFilter::NeedsCaseStudy => $query->whereRaw('not ('.$filled('content').')'),
            ProjectReadinessFilter::NeedsDetails => $query->where(fn (Builder $query) => $query->whereRaw("not ({$details})")
                ->orWhereDoesntHave('tags')),
            default => null,
        };
    }

    /**
     * @param  literal-string  $column
     * @return literal-string
     */
    private function filledSql(string $column): string
    {
        return "trim(coalesce({$column}, ''), char(32, 9, 10, 11, 13, 0)) <> ''";
    }
}
