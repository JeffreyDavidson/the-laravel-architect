<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Queries\EpisodeNavigationQuery;

class PodcastEpisodeViewModel
{
    public function __construct(private EpisodeNavigationQuery $episodeNavigationQuery) {}

    /** @return array{podcast: Podcast, episode: Episode, nextEpisode: ?Episode, prevEpisode: ?Episode, seoSource: Episode} */
    public function data(Podcast $podcast, Episode $episode): array
    {
        $episode->load(['podcast', 'tags']);

        $navigation = $this->episodeNavigationQuery->get($podcast, $episode);

        return [
            'podcast' => $podcast,
            'episode' => $episode,
            'nextEpisode' => $navigation['next'],
            'prevEpisode' => $navigation['previous'],
            'seoSource' => $episode,
        ];
    }
}
