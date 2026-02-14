<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $subscriber = Subscriber::withTrashed ?? Subscriber::where('email', $validated['email'])->first();

        if ($subscriber = Subscriber::where('email', $validated['email'])->first()) {
            if ($subscriber->unsubscribed_at) {
                $subscriber->update(['unsubscribed_at' => null, 'subscribed_at' => now()]);
                return back()->with('newsletter_success', 'Welcome back! You\'ve been re-subscribed.');
            }
            return back()->with('newsletter_success', 'You\'re already subscribed!');
        }

        Subscriber::create([
            'email' => $validated['email'],
            'subscribed_at' => now(),
        ]);

        return back()->with('newsletter_success', 'You\'re in! Thanks for subscribing.');
    }
}
