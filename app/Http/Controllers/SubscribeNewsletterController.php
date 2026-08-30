<?php

namespace App\Http\Controllers;

use App\Actions\RequestNewsletterSubscription;
use App\Http\Requests\SubscribeNewsletterRequest;
use Illuminate\Http\RedirectResponse;

class SubscribeNewsletterController extends Controller
{
    public function __invoke(
        SubscribeNewsletterRequest $request,
        RequestNewsletterSubscription $requestNewsletterSubscription,
    ): RedirectResponse {
        if ($request->filled('website')) {
            return back()->with('newsletter_success', 'Check your email to confirm your subscription.');
        }

        $email = $request->safe()->string('email')->lower()->toString();

        $requestNewsletterSubscription($email);

        return back()->with('newsletter_success', 'Check your email to confirm your subscription.');
    }
}
