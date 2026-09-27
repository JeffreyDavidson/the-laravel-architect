<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Database\Eloquent\Builder;

class EpisodeNavigationQuery
{
    /** @return array{previous: Episode|null, next: Episode|null} */
    public function get(Podcast $podcast, Episode $episode): array
    {
        if ($episode->published_at === null) {
            return [
                'previous' => null,
                'next' => null,
            ];
        }

        return [
            'previous' => $podcast->publishedEpisodes()
                ->where(fn (Builder $query) => $query->where('published_at', '<', $episode->published_at)
                    ->orWhere(fn (Builder $query) => $query->where('published_at', $episode->published_at)
                        ->where('id', '<', $episode->id)))
                ->latest('published_at')
                ->latest('id')
                ->first(),
            'next' => $podcast->publishedEpisodes()
                ->where(fn (Builder $query) => $query->where('published_at', '>', $episode->published_at)
                    ->orWhere(fn (Builder $query) => $query->where('published_at', $episode->published_at)
                        ->where('id', '>', $episode->id)))
                ->oldest('published_at')
                ->oldest('id')
                ->first(),
        ];
    }
}
