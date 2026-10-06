<?php

namespace App\Http\Controllers;

use App\ViewModels\NewsletterConfirmedViewModel;
use Illuminate\Contracts\View\View;

class NewsletterConfirmedController
{
    public function __invoke(NewsletterConfirmedViewModel $viewModel): View
    {
        return view('pages.newsletter.confirmed', $viewModel->data());
    }
}
