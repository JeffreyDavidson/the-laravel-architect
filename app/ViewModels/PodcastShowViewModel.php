<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Support\Seo\PaginatedPageSeo;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PodcastShowViewModel
{
    /**
     * @return array{
     *     podcast: Podcast,
     *     episodes: LengthAwarePaginator<int, Episode>,
     *     latestEpisode: Episode|null,
     *     seoSource: SEOData,
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
        $canonicalUrl = $page->url('podcast.show', ['podcast' => $podcast]);

        return [
            'podcast' => $podcast,
            'episodes' => $episodes,
            'latestEpisode' => $episodes->onFirstPage() ? $episodes->first() : null,
            'seoSource' => new SEOData(
                title: $page->title($podcast->name),
                description: $page->description($podcast->description),
                image: $podcast->cover_image_url,
                url: $canonicalUrl,
                canonical_url: $canonicalUrl,
            ),
        ];
    }
}
