<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class NewsletterController extends Controller
{
    public function showConfirmation(Request $request, Subscriber $subscriber, string $token): View
    {
        $seoSource = (new SEOData(
            title: 'Confirm Your Subscription',
            description: 'Confirm your subscription to The Laravel Architect newsletter.',
        ))->markAsNoindex();

        return view('newsletter.confirm', [
            'actionUrl' => $request->fullUrl(),
            'seoSource' => $seoSource,
            'subscriber' => $subscriber,
        ]);
    }

    public function confirm(Subscriber $subscriber, string $token): RedirectResponse
    {
        $subscriber->update([
            'verified_at' => now(),
            'verification_token' => null,
            'unsubscribed_at' => null,
        ]);

        return redirect()->route('home')->with('newsletter_success', 'You\'re subscribed. Thanks for confirming!');
    }

    public function showUnsubscribe(Request $request, Subscriber $subscriber): View
    {
        $seoSource = (new SEOData(
            title: 'Unsubscribe',
            description: 'Manage your subscription to The Laravel Architect newsletter.',
        ))->markAsNoindex();

        return view('newsletter.unsubscribe', [
            'actionUrl' => $request->fullUrl(),
            'seoSource' => $seoSource,
            'subscriber' => $subscriber,
        ]);
    }

    public function unsubscribe(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->update([
            'verification_token' => null,
            'unsubscribed_at' => now(),
        ]);

        return redirect()->route('home')->with('newsletter_success', 'You have been unsubscribed.');
    }
}
