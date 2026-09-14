<?php

namespace App\ViewModels;

use App\Models\NewsletterIssue;

class NewsletterIssueViewModel
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
}
