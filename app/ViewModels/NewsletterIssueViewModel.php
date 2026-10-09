<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\NewsletterIssue;
use JeffreyDavidson\CreatorKit\Contracts\PageViewModel;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use JeffreyDavidson\CreatorKit\ViewModels\Concerns\AppliesStoredSeo;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class NewsletterIssueViewModel implements PageViewModel
{
    use AppliesStoredSeo;

    /**
     * The issue page, keeping any SEO fields saved in the admin.
     *
     * @return array{issue: NewsletterIssue, pageMeta: PageMeta}
     */
    public function data(NewsletterIssue $issue): array
    {
        return [
            'issue' => $issue,
            'pageMeta' => new PageMeta($this->withStoredSeo($issue, new SEOData(
                title: $issue->title,
                description: $issue->excerpt,
            ))),
        ];
    }

    /** @return array{issue: NewsletterIssue, pageMeta: PageMeta} */
    public function previewData(NewsletterIssue $issue): array
    {
        return [
            'issue' => $issue,
            'pageMeta' => new PageMeta(new SEOData(
                title: $issue->title.' — Preview',
                description: $issue->excerpt,
                robots: 'noindex, nofollow',
            )),
        ];
    }
}
