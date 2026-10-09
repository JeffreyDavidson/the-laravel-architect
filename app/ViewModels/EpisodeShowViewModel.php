<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Presenters\EpisodePresenter;
use App\Presenters\PodcastPresenter;
use App\Queries\EpisodeNavigationQuery;
use JeffreyDavidson\CreatorKit\Contracts\PageViewModel;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use JeffreyDavidson\CreatorKit\ViewModels\Concerns\AppliesStoredSeo;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class EpisodeShowViewModel implements PageViewModel
{
    use AppliesStoredSeo;

    public function __construct(
        private EpisodeNavigationQuery $episodeNavigationQuery,
        private SiteStructuredData $site,
    ) {}

    /**
     * The episode page, keeping any SEO fields saved in the admin, described as an episode of its
     * show's series.
     *
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
     *     pageMeta: PageMeta,
     * }
     */
    public function data(Podcast $podcast, Episode $episode): array
    {
        $data = $this->pageData($podcast, $episode);
        $podcastUrl = route('podcasts.show', $podcast);

        return [
            ...$data,
            'pageMeta' => new PageMeta(
                seo: $this->withStoredSeo($episode, new SEOData(
                    title: "{$episode->title} — {$podcast->name}",
                    description: $episode->description,
                )),
                structuredData: [
                    $data['podcastPresenter']->seriesSchema($this->site->authorReference()),
                    $data['episodePresenter']->episodeSchema($podcast),
                    $this->site->breadcrumbs([
                        ['name' => 'Podcast', 'url' => route('podcasts.index')],
                        ['name' => $podcast->name, 'url' => $podcastUrl],
                        ['name' => $episode->title, 'url' => route('podcasts.episode', [$podcast, $episode])],
                    ]),
                ],
            ),
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
     *     pageMeta: PageMeta,
     * }
     */
    public function previewData(Podcast $podcast, Episode $episode): array
    {
        return [
            ...$this->pageData($podcast, $episode),
            'pageMeta' => new PageMeta(new SEOData(
                title: $episode->title.' — Preview',
                description: $episode->description,
                robots: 'noindex, nofollow',
            )),
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
     * }
     */
    private function pageData(Podcast $podcast, Episode $episode): array
    {
        $episode->load(['podcast', 'tags']);

        $navigation = $this->episodeNavigationQuery->get($podcast, $episode);
        $episodePresenter = EpisodePresenter::from($episode);
        // The Transistor player is the only podcast player (YouTube is handled by the page).
        $embedUrl = $episodePresenter->transistorEmbedUrl();
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
        ];
    }
}
