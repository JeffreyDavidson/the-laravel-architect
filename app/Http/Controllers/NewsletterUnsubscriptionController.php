<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UnsubscribeFromNewsletter;
use App\Models\Subscriber;
use App\ViewModels\NewsletterUnsubscriptionViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The signed unsubscribe page and the form it submits. One-click requests from
 * mail providers go to NewsletterOneClickUnsubscriptionController instead.
 */
final class NewsletterUnsubscriptionController
{
    public function create(Request $request, Subscriber $subscriber, NewsletterUnsubscriptionViewModel $viewModel): View
    {
        return view('pages.newsletter.unsubscribe', $viewModel->data($subscriber, $request->fullUrl()));
    }

    public function store(
        Subscriber $subscriber,
        UnsubscribeFromNewsletter $unsubscribeFromNewsletter,
    ): RedirectResponse {
        $unsubscribeFromNewsletter->handle($subscriber);

        return redirect()->route('home')
            ->withFragment('newsletter-form')
            ->with('newsletter_success', 'You have been unsubscribed.');
    }
}
