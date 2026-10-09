<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Podcast;
use JeffreyDavidson\CreatorKit\Contracts\PageViewModel;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use JeffreyDavidson\CreatorKit\Support\Seo\CollectionListing;
use JeffreyDavidson\CreatorKit\Support\Seo\JsonLd;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class PodcastIndexViewModel implements PageViewModel
{
    public function __construct(private SiteStructuredData $site) {}

    /**
     * @return array{
     *     podcast: Podcast|null,
     *     pageMeta: PageMeta,
     * }
     */
    public function data(): array
    {
        $podcast = Podcast::query()->active()
            ->withCount('publishedEpisodes')
            ->orderBy('sort_order')
            ->first();
        $url = route('podcasts.index');

        return [
            'podcast' => $podcast,
            'pageMeta' => new PageMeta(
                seo: new SEOData(
                    title: 'Podcast',
                    description: 'Coffee with The Laravel Architect from Jeffrey Davidson — deep dives into Laravel, PHP, architecture patterns, and the craft of building modern web applications.',
                ),
                structuredData: [
                    ...JsonLd::collectionPage(new CollectionListing(
                        'Podcast',
                        $url,
                        $podcast instanceof Podcast
                            ? [['name' => $podcast->name, 'url' => route('podcasts.show', $podcast)]]
                            : [],
                    )),
                    $this->site->breadcrumbs([['name' => 'Podcast', 'url' => $url]]),
                ],
            ),
        ];
    }
}
