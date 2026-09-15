<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Queries\EpisodeNavigationQuery;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PodcastEpisodeViewModel
{
    public function __construct(private readonly EpisodeNavigationQuery $episodeNavigationQuery) {}

    /** @return array{podcast: Podcast, episode: Episode, nextEpisode: ?Episode, prevEpisode: ?Episode, seoSource: Episode|SEOData} */
    public function data(Podcast $podcast, Episode $episode, bool $preview = false): array
    {
        $episode->load(['podcast', 'tags']);

        $navigation = $this->episodeNavigationQuery->get($podcast, $episode);

        return [
            'podcast' => $podcast,
            'episode' => $episode,
            'nextEpisode' => $navigation['next'],
            'prevEpisode' => $navigation['previous'],
            'seoSource' => $preview
                ? new SEOData(
                    title: $episode->title.' — Preview',
                    description: $episode->description,
                    robots: 'noindex, nofollow',
                )
                : $episode,
        ];
    }
}
