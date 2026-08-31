<?php

namespace App\ViewModels;

use App\Models\Podcast;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PodcastIndexViewModel
{
    /**
     * @return array{
     *     podcast: Podcast|null,
     *     seoSource: SEOData,
     * }
     */
    public function data(): array
    {
        return [
            'podcast' => Podcast::active()
                ->withCount('publishedEpisodes')
                ->orderBy('sort_order')
                ->first(),
            'seoSource' => new SEOData(
                title: 'Podcast',
                description: 'Coffee with The Laravel Architect from Jeffrey Davidson — deep dives into Laravel, PHP, architecture patterns, and the craft of building modern web applications.',
            ),
        ];
    }
}
