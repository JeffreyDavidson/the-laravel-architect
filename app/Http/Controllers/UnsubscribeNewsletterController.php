<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;

class UnsubscribeNewsletterController extends Controller
{
    public function __invoke(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->fill([
            'unsubscribed_at' => now(),
        ]);
        $subscriber->verification_token_hash = null;
        $subscriber->save();

        return redirect()->route('home')->with('newsletter_success', 'You have been unsubscribed.');
    }
}
