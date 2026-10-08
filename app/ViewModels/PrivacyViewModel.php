<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Contracts\PageViewModel;
use App\Data\PageMeta;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class PrivacyViewModel implements PageViewModel
{
    public function __construct(private SiteStructuredData $site) {}

    /** @return array{pageMeta: PageMeta} */
    public function data(): array
    {
        $url = route('privacy');

        return [
            'pageMeta' => new PageMeta(
                seo: new SEOData(
                    title: 'Privacy',
                    description: 'How The Laravel Architect handles contact messages, newsletter subscriptions, observability, and essential site data.',
                ),
                structuredData: [
                    $this->site->page('WebPage', 'Privacy', $url),
                    $this->site->breadcrumbs([['name' => 'Privacy', 'url' => $url]]),
                ],
            ),
        ];
    }
}
