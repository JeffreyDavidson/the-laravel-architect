<?php

namespace App\Http\Controllers;

use App\Actions\RequestNewsletterSubscription;
use App\Actions\UnsubscribeFromNewsletter;
use App\Http\Requests\SubscribeNewsletterRequest;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NewsletterSubscriptionController
{
    public function store(
        SubscribeNewsletterRequest $request,
        RequestNewsletterSubscription $requestNewsletterSubscription,
    ): JsonResponse|RedirectResponse {
        $message = 'Check your email to confirm your subscription.';

        if (! $request->filled('website')) {
            $requestNewsletterSubscription->handle(
                $request->safe()
                    ->string('email')
                    ->lower()
                    ->toString(),
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()
            ->withFragment('newsletter-form')
            ->with('newsletter_success', $message);
    }

    public function destroy(
        Subscriber $subscriber,
        UnsubscribeFromNewsletter $unsubscribeFromNewsletter,
    ): RedirectResponse {
        $unsubscribeFromNewsletter->handle($subscriber);

        return redirect()->route('home')
            ->withFragment('newsletter-form')
            ->with('newsletter_success', 'You have been unsubscribed.');
    }
}
