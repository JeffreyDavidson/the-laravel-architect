<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Presenters\EpisodePresenter;
use App\Presenters\PodcastPresenter;
use App\Queries\EpisodeNavigationQuery;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class EpisodeShowViewModel
{
    public function __construct(private readonly EpisodeNavigationQuery $episodeNavigationQuery) {}

    /**
     * @return array{
     *     podcast: Podcast,
     *     podcastPresenter: PodcastPresenter,
     *     episode: Episode,
     *     episodePresenter: EpisodePresenter,
     *     nextEpisode: ?Episode,
     *     prevEpisode: ?Episode,
     *     embedUrl: ?string,
     *     youtubeVideoId: ?string,
     *     showDescriptionFallback: bool,
     *     showDetailsComingSoon: bool,
     *     seoSource: Episode,
     * }
     */
    public function data(Podcast $podcast, Episode $episode): array
    {
        $episode->load(['podcast', 'tags']);

        $navigation = $this->episodeNavigationQuery->get($podcast, $episode);
        $episodePresenter = EpisodePresenter::from($episode);
        // The Transistor player is the only podcast player (YouTube is handled by the page).
        $embedUrl = $episode->transistorEmbedUrl();
        // With no player, video, show notes or transcript, the description stands in for the episode.
        $showDescriptionFallback = ! $embedUrl
            && ! $episode->show_notes
            && ! $episode->transcript
            && ! $episodePresenter->hasYouTube();

        return [
            'podcast' => $podcast,
            'podcastPresenter' => PodcastPresenter::from($podcast),
            'episode' => $episode,
            'episodePresenter' => $episodePresenter,
            'nextEpisode' => $navigation['next'],
            'prevEpisode' => $navigation['previous'],
            'embedUrl' => $embedUrl,
            'youtubeVideoId' => $episodePresenter->youtubeVideoId(),
            'showDescriptionFallback' => $showDescriptionFallback,
            'showDetailsComingSoon' => $showDescriptionFallback
                && ! $episode->guest_name
                && $episode->tags->isEmpty(),
            'seoSource' => $episode,
        ];
    }

    /**
     * @return array{
     *     podcast: Podcast,
     *     podcastPresenter: PodcastPresenter,
     *     episode: Episode,
     *     episodePresenter: EpisodePresenter,
     *     nextEpisode: ?Episode,
     *     prevEpisode: ?Episode,
     *     embedUrl: ?string,
     *     youtubeVideoId: ?string,
     *     showDescriptionFallback: bool,
     *     showDetailsComingSoon: bool,
     *     seoSource: SEOData,
     * }
     */
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
