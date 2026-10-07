<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ViewModels\NewsletterConfirmedViewModel;
use Illuminate\Contracts\View\View;

final class NewsletterConfirmedController
{
    public function __invoke(NewsletterConfirmedViewModel $viewModel): View
    {
        return view('pages.newsletter.confirmed', $viewModel->data());
    }
}
