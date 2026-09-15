<?php

namespace App\ViewModels;

use App\Models\NewsletterIssue;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class NewsletterIssueViewModel
{
    /**
     * @return array{issue: NewsletterIssue, seoSource: NewsletterIssue|SEOData}
     */
    public function data(NewsletterIssue $issue, bool $preview = false): array
    {
        return [
            'issue' => $issue,
            'seoSource' => $preview
                ? new SEOData(
                    title: $issue->title.' — Preview',
                    description: $issue->excerpt,
                    robots: 'noindex, nofollow',
                )
                : $issue,
        ];
    }
}
