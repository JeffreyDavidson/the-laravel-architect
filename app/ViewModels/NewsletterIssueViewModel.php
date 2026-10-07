<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\NewsletterIssue;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class NewsletterIssueViewModel
{
    /**
     * @return array{issue: NewsletterIssue, seoSource: NewsletterIssue}
     */
    public function data(NewsletterIssue $issue): array
    {
        return [
            'issue' => $issue,
            'seoSource' => $issue,
        ];
    }

    /** @return array{issue: NewsletterIssue, seoSource: SEOData} */
    public function previewData(NewsletterIssue $issue): array
    {
        $data = $this->data($issue);
        $data['seoSource'] = new SEOData(
            title: $issue->title.' — Preview',
            description: $issue->excerpt,
            robots: 'noindex, nofollow',
        );

        return $data;
    }
}
