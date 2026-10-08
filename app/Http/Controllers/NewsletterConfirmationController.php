<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use App\ViewModels\NewsletterConfirmationViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class NewsletterConfirmationController
{
    public function create(Request $request, Subscriber $subscriber, NewsletterConfirmationViewModel $viewModel): View
    {
        return view('pages.newsletter.confirm', $viewModel->data($subscriber, $request->fullUrl()));
    }

    public function store(
        Subscriber $subscriber,
        ConfirmNewsletterSubscription $confirmNewsletterSubscription,
    ): RedirectResponse {
        $confirmNewsletterSubscription->handle($subscriber);

        return redirect()->route('newsletter.confirmed');
    }
}
