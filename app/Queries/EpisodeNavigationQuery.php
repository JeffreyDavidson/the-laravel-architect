<?php

namespace App\Queries;

use App\Models\Episode;
use App\Models\Podcast;

class EpisodeNavigationQuery
{
    /** @return array{previous: Episode|null, next: Episode|null} */
    public function get(Podcast $podcast, Episode $episode): array
    {
        return [
            'previous' => $podcast->publishedEpisodes()
                ->where('published_at', '<', $episode->published_at)
                ->latest('published_at')
                ->first(),
            'next' => $podcast->publishedEpisodes()
                ->where('published_at', '>', $episode->published_at)
                ->oldest('published_at')
                ->first(),
        ];
    }
}
