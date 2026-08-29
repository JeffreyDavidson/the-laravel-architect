<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;

class ConfirmNewsletterSubscriptionController extends Controller
{
    public function __invoke(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->fill([
            'verified_at' => now(),
            'unsubscribed_at' => null,
        ]);
        $subscriber->verification_token_hash = null;
        $subscriber->save();

        return redirect()->route('home')->with('newsletter_success', 'You\'re subscribed. Thanks for confirming!');
    }
}
