<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Collection;

/**
 * The most recently edited posts, episodes, newsletter issues and projects, newest first,
 * for the admin dashboard's activity list.
 */
final readonly class RecentlyEditedContentQuery
{
    /** @return Collection<int, Post|Episode|NewsletterIssue|Project> */
    public function get(int $limit): Collection
    {
        /** @var Collection<int, Post|Episode|NewsletterIssue|Project> $records */
        $records = collect();

        foreach ([Post::class, Episode::class, NewsletterIssue::class, Project::class] as $model) {
            $records = $records->concat(
                $model::query()
                    ->latest('updated_at')
                    ->take($limit)
                    ->get(),
            );
        }

        return $records->sortByDesc('updated_at')
            ->take($limit)
            ->values();
    }
}
