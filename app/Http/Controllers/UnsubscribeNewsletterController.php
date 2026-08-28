<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;

class UnsubscribeNewsletterController extends Controller
{
    public function __invoke(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->update([
            'verification_token' => null,
            'unsubscribed_at' => now(),
        ]);

        return redirect()->route('home')->with('newsletter_success', 'You have been unsubscribed.');
    }
}
