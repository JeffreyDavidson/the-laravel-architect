<?php

namespace App\ViewModels;

use App\Models\NewsletterIssue;
use Illuminate\Pagination\LengthAwarePaginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class NewsletterIndexViewModel
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

        abort_if($issues->currentPage() > $issues->lastPage(), 404);

        return [
            'issues' => $issues,
            'seoSource' => new SEOData(
                title: 'Newsletter Archive',
                description: 'Practical Laravel architecture notes, tutorials, and updates from Jeffrey Davidson.',
                url: route('newsletter.index'),
                canonical_url: route('newsletter.index'),
            ),
        ];
    }
}
