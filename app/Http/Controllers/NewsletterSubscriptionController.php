<?php

namespace App\Http\Controllers;

use App\Actions\RequestNewsletterSubscription;
use App\Actions\UnsubscribeFromNewsletter;
use App\Http\Requests\SubscribeNewsletterRequest;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;

class NewsletterSubscriptionController
{
    public function store(
        SubscribeNewsletterRequest $request,
        RequestNewsletterSubscription $requestNewsletterSubscription,
    ): RedirectResponse {
        if ($request->filled('website')) {
            return back()->with('newsletter_success', 'Check your email to confirm your subscription.');
        }

        $email = $request->safe()->string('email')->lower()->toString();

        $requestNewsletterSubscription->handle($email);

        return back()->with('newsletter_success', 'Check your email to confirm your subscription.');
    }

    public function destroy(
        Subscriber $subscriber,
        UnsubscribeFromNewsletter $unsubscribeFromNewsletter,
    ): RedirectResponse {
        $unsubscribeFromNewsletter->handle($subscriber);

        return redirect()->route('home')->with('newsletter_success', 'You have been unsubscribed.');
    }
}
