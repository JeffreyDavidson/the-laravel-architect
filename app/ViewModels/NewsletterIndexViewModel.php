<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\NewsletterIssue;
use App\Support\Seo\PaginatedPageSeo;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class NewsletterIndexViewModel
{
    private const int ISSUES_PER_PAGE = 12;

    /**
     * @return array{issues: LengthAwarePaginator<int, NewsletterIssue>, seoSource: SEOData}
     */
    public function data(): array
    {
        $issues = NewsletterIssue::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(self::ISSUES_PER_PAGE);

        $page = PaginatedPageSeo::forCurrentPage($issues);
        $url = $page->url('newsletter.index');

        return [
            'issues' => $issues,
            'seoSource' => new SEOData(
                title: $page->title('Newsletter Archive'),
                description: 'Practical Laravel architecture notes, tutorials, and updates from Jeffrey Davidson.',
                url: $url,
                canonical_url: $url,
            ),
        ];
    }
}
