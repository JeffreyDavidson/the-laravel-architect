<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RequestNewsletterSubscription;
use App\Http\Requests\SubscribeNewsletterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

final class NewsletterSubscriptionController
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
}
