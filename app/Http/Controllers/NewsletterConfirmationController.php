<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use App\ViewModels\NewsletterConfirmationViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterConfirmationController
{
    public function create(Request $request, Subscriber $subscriber, NewsletterConfirmationViewModel $viewModel): View
    {
        return view('newsletter.confirm', $viewModel->data($subscriber, $request->fullUrl()));
    }

    public function store(
        Subscriber $subscriber,
        ConfirmNewsletterSubscription $confirmNewsletterSubscription,
    ): RedirectResponse {
        $confirmNewsletterSubscription->handle($subscriber);

        return redirect()->route('home')->with('newsletter_success', 'You\'re subscribed. Thanks for confirming!');
    }
}
