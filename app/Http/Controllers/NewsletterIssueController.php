<?php

namespace App\Http\Controllers;

use App\Models\NewsletterIssue;
use App\ViewModels\NewsletterIndexViewModel;
use App\ViewModels\NewsletterIssueViewModel;
use Illuminate\Contracts\View\View;

class NewsletterIssueController
{
    public function index(NewsletterIndexViewModel $viewModel): View
    {
        return view('pages.newsletter.index', $viewModel->data());
    }

    public function show(NewsletterIssue $newsletterIssue, NewsletterIssueViewModel $viewModel): View
    {
        abort_unless($newsletterIssue->isPublished(), 404);

        return view('pages.newsletter.issue', $viewModel->data($newsletterIssue));
    }
}
