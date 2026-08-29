<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
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
            ->paginate(20);

        $latestEpisode = $episodes->onFirstPage() ? $episodes->first() : null;
        $canonicalUrl = $episodes->onFirstPage()
            ? route('podcast.show', $podcast)
            : route('podcast.show', ['podcast' => $podcast, 'page' => $episodes->currentPage()]);
        $title = $podcast->name;
        $description = $podcast->description;

        if (! $episodes->onFirstPage()) {
            $title .= " — Page {$episodes->currentPage()}";
            $description = trim(($description ?? '')." Page {$episodes->currentPage()} of {$episodes->lastPage()}.");
        }

        return [
            'podcast' => $podcast,
            'episodes' => $episodes,
            'latestEpisode' => $latestEpisode,
            'seoSource' => new SEOData(
                title: $title,
                description: $description,
                url: $canonicalUrl,
                canonical_url: $canonicalUrl,
            ),
        ];
    }
}
