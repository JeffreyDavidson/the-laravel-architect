<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class NewsletterController extends Controller
{
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
