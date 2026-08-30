<?php

namespace App\Http\Controllers;

use App\Actions\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;

class ConfirmNewsletterSubscriptionController extends Controller
{
    public function __invoke(
        Subscriber $subscriber,
        ConfirmNewsletterSubscription $confirmNewsletterSubscription,
    ): RedirectResponse {
        $confirmNewsletterSubscription($subscriber);

        return redirect()->route('home')->with('newsletter_success', 'You\'re subscribed. Thanks for confirming!');
    }
}
