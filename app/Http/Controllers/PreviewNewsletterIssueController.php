<?php

namespace App\Http\Controllers;

use App\Models\NewsletterIssue;
use App\ViewModels\NewsletterIssueViewModel;
use Illuminate\Contracts\View\View;

class PreviewNewsletterIssueController
{
    public function __invoke(NewsletterIssue $newsletterIssue, NewsletterIssueViewModel $viewModel): View
    {
        return view('newsletter.issue', $viewModel->previewData($newsletterIssue));
    }
}
