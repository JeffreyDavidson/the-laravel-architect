<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Queries\EpisodeNavigationQuery;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PodcastEpisodeViewModel
{
    public function __construct(private readonly EpisodeNavigationQuery $episodeNavigationQuery) {}

    /** @return array{podcast: Podcast, episode: Episode, nextEpisode: ?Episode, prevEpisode: ?Episode, audioUrl: ?string, embedUrl: ?string, embedLink: ?string, seoSource: Episode} */
    public function data(Podcast $podcast, Episode $episode): array
    {
        $episode->load(['podcast', 'tags']);

        $navigation = $this->episodeNavigationQuery->get($podcast, $episode);

        return [
            'podcast' => $podcast,
            'episode' => $episode,
            'nextEpisode' => $navigation['next'],
            'prevEpisode' => $navigation['previous'],
            'audioUrl' => $episode->publicAudioUrl(),
            'embedUrl' => $episode->publicEmbedUrl(),
            'embedLink' => $episode->publicEmbedLink(),
            'seoSource' => $episode,
        ];
    }

    /** @return array{podcast: Podcast, episode: Episode, nextEpisode: ?Episode, prevEpisode: ?Episode, audioUrl: ?string, embedUrl: ?string, embedLink: ?string, seoSource: SEOData} */
    public function previewData(Podcast $podcast, Episode $episode): array
    {
        $data = $this->data($podcast, $episode);
        $data['seoSource'] = new SEOData(
            title: $episode->title.' — Preview',
            description: $episode->description,
            robots: 'noindex, nofollow',
        );

        return $data;
    }
}
