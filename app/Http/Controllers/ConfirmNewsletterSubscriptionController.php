<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;

class ConfirmNewsletterSubscriptionController extends Controller
{
    public function __invoke(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->update([
            'verified_at' => now(),
            'verification_token' => null,
            'unsubscribed_at' => null,
        ]);

        return redirect()->route('home')->with('newsletter_success', 'You\'re subscribed. Thanks for confirming!');
    }
}
