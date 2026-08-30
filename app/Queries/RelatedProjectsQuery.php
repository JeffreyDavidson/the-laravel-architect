<?php

namespace App\Queries;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class RelatedProjectsQuery
{
    /** @return Collection<int, Project> */
    public function get(Project $project, int $limit = 3): Collection
    {
        if ($limit < 1) {
            return new Collection;
        }

        return Project::published()
            ->whereKeyNot($project->getKey())
            ->with('tags')
            ->orderBy('sort_order')
            ->take($limit)
            ->get();
    }
}
