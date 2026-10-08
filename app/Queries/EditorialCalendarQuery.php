<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Episode;
use App\Models\Post;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EditorialCalendarQuery
{
    /**
     * Posts and episodes published between the given display-timezone days, plus undated content:
     * the posts first, then the episodes, each ordered by publication date and ID. The caller places
     * them on display days and links them to the admin.
     *
     * @return Collection<int, Post|Episode>
     */
    public function get(CarbonInterface $from, CarbonInterface $until): Collection
    {
        $range = [
            $from->copy()
                ->startOfDay()
                ->utc(),
            $until->copy()
                ->endOfDay()
                ->utc(),
        ];

        /** @var Collection<int, Post|Episode> $posts */
        $posts = $this->datedWithinOrUndated(Post::query(), $range)
            ->get()
            ->toBase();

        return $posts->concat(
            $this->datedWithinOrUndated(Episode::query(), $range)
                ->get(),
        );
    }

    /**
     * @template TModel of Post|Episode
     *
     * @param  Builder<TModel>  $query
     * @param  array{CarbonInterface, CarbonInterface}  $range
     * @return Builder<TModel>
     */
    private function datedWithinOrUndated(Builder $query, array $range): Builder
    {
        return $query
            ->select(['id', 'title', 'status', 'published_at'])
            ->where(fn (Builder $query): Builder => $query
                ->whereBetween('published_at', $range)
                ->orWhereNull('published_at'))
            ->orderBy('published_at')
            ->orderBy('id');
    }
}
