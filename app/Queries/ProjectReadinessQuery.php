<?php

namespace App\Queries;

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

        match ($filter) {
            'ready' => $query->whereRaw($filled('featured_image_path'))->whereRaw($filled('content'))->whereRaw($details)->whereHas('tags'),
            'needs_image' => $query->whereRaw('not ('.$filled('featured_image_path').')'),
            'needs_case_study' => $query->whereRaw('not ('.$filled('content').')'),
            'needs_details' => $query->where(fn (Builder $query) => $query->whereRaw("not ({$details})")->orWhereDoesntHave('tags')),
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
