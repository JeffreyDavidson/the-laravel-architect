<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Queries\EpisodeNavigationQuery;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class EpisodeShowViewModel
{
    public function __construct(private readonly EpisodeNavigationQuery $episodeNavigationQuery) {}

    /** @return array{podcast: Podcast, episode: Episode, nextEpisode: ?Episode, prevEpisode: ?Episode, audioUrl: ?string, embedUrl: ?string, embedLink: ?string, seoSource: Episode} */
    public function data(Podcast $podcast, Episode $episode): array
    {
        $episode->load(['podcast', 'tags']);

        $navigation = $this->episodeNavigationQuery->get($podcast, $episode);
        $transistorEmbedUrl = $episode->transistorEmbedUrl();

        return [
            'podcast' => $podcast,
            'episode' => $episode,
            'nextEpisode' => $navigation['next'],
            'prevEpisode' => $navigation['previous'],
            // A Transistor player replaces the uploaded-audio player and the Spotify/Apple
            // embed and link; those fields stay stored until the backfill is confirmed.
            'audioUrl' => $transistorEmbedUrl === null ? $episode->publicAudioUrl() : null,
            'embedUrl' => $transistorEmbedUrl ?? $episode->publicEmbedUrl(),
            'embedLink' => $transistorEmbedUrl === null ? $episode->publicEmbedLink() : null,
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
