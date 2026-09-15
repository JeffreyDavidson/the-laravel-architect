<?php

namespace App\Queries;

use App\Models\Episode;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RelatedProjectContentQuery
{
    /**
     * @return array{
     *     posts: Collection<int, Post>,
     *     episodes: Collection<int, Episode>,
     * }
     */
    public function get(Project $project, int $limit = 3): array
    {
        $empty = [
            'posts' => new Collection,
            'episodes' => new Collection,
        ];

        if ($limit < 1) {
            return $empty;
        }

        $project->loadMissing('tags');

        if ($project->tags->isEmpty()) {
            return $empty;
        }

        return [
            'posts' => Post::published()
                ->withAnyTags($project->tags)
                ->with(['category', 'tags'])
                ->latest('published_at')
                ->take($limit)
                ->get(),
            'episodes' => Episode::published()
                ->whereHas('podcast', function (Builder $query): void {
                    $query->where('is_active', true);
                })
                ->withAnyTags($project->tags)
                ->with(['podcast', 'tags'])
                ->latest('published_at')
                ->take($limit)
                ->get(),
        ];
    }
}
