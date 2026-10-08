<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Contracts\PageViewModel;
use App\Data\PageMeta;
use App\Models\NewsletterIssue;
use App\Support\Seo\PaginatedPageSeo;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class NewsletterIndexViewModel implements PageViewModel
{
    private const int ISSUES_PER_PAGE = 12;

    /**
     * @return array{issues: LengthAwarePaginator<int, NewsletterIssue>, pageMeta: PageMeta}
     */
    public function data(): array
    {
        $issues = NewsletterIssue::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(self::ISSUES_PER_PAGE);

        $page = PaginatedPageSeo::forCurrentPage($issues);
        abort_if($page->isOutOfRange(), 404);
        $url = $page->url('newsletter.index');

        return [
            'issues' => $issues,
            'pageMeta' => new PageMeta(new SEOData(
                title: $page->title('Newsletter Archive'),
                description: 'Practical Laravel architecture notes, tutorials, and updates from Jeffrey Davidson.',
                url: $url,
                canonical_url: $url,
            )),
        ];
    }
}
