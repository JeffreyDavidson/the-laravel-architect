<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Presenters\PodcastPresenter;
use Illuminate\Pagination\LengthAwarePaginator;
use JeffreyDavidson\CreatorKit\Contracts\PageViewModel;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use JeffreyDavidson\CreatorKit\Support\Seo\CollectionListing;
use JeffreyDavidson\CreatorKit\Support\Seo\JsonLd;
use JeffreyDavidson\CreatorKit\Support\Seo\PaginatedPageSeo;
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
        $canonicalUrl = $page->url('podcasts.show', ['podcast' => $podcast]);
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
                            'url' => route('podcasts.episode', [$podcast, $episode]),
                        ],
                    )),
                    $this->site->breadcrumbs([
                        ['name' => 'Podcast', 'url' => route('podcasts.index')],
                        ['name' => $podcast->name, 'url' => $canonicalUrl],
                    ]),
                ],
            ),
        ];
    }
}
