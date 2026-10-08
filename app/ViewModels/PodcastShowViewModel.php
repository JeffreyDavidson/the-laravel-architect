<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Contracts\PageViewModel;
use App\Data\PageMeta;
use App\Models\Episode;
use App\Models\Podcast;
use App\Presenters\PodcastPresenter;
use App\Support\Seo\CollectionListing;
use App\Support\Seo\JsonLd;
use App\Support\Seo\PaginatedPageSeo;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class PodcastShowViewModel implements PageViewModel
{
    public function __construct(private SiteStructuredData $site) {}

    /**
     * @return array{
     *     podcast: Podcast,
     *     episodes: LengthAwarePaginator<int, Episode>,
     *     latestEpisode: Episode|null,
     *     pageMeta: PageMeta,
     * }
     */
    public function data(Podcast $podcast): array
    {
        $episodes = $podcast->publishedEpisodes()
            ->with('tags')
            ->latest('published_at')
            ->latest('id')
            ->paginate(20);

        $page = PaginatedPageSeo::forCurrentPage($episodes);
        abort_if($page->isOutOfRange(), 404);
        $canonicalUrl = $page->url('podcast.show', ['podcast' => $podcast]);
        $podcastPresenter = PodcastPresenter::from($podcast);

        return [
            'podcast' => $podcast,
            'episodes' => $episodes,
            'latestEpisode' => $episodes->onFirstPage() ? $episodes->first() : null,
            'pageMeta' => new PageMeta(
                seo: new SEOData(
                    title: $page->title($podcast->name),
                    description: $page->description($podcast->description),
                    image: $podcastPresenter->coverImageUrl(),
                    url: $canonicalUrl,
                    canonical_url: $canonicalUrl,
                ),
                structuredData: [
                    $podcastPresenter->seriesSchema($this->site->authorReference()),
                    ...JsonLd::collectionPage(CollectionListing::paginated(
                        "{$podcast->name} Episodes",
                        $canonicalUrl,
                        $episodes,
                        static fn (Episode $episode): array => [
                            'name' => $episode->title,
                            'url' => route('podcast.episode', [$podcast, $episode]),
                        ],
                    )),
                    $this->site->breadcrumbs([
                        ['name' => 'Podcast', 'url' => route('podcast.index')],
                        ['name' => $podcast->name, 'url' => $canonicalUrl],
                    ]),
                ],
            ),
        ];
    }
}
