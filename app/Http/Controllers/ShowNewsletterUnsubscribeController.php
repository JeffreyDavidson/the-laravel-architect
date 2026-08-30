<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowNewsletterUnsubscribeController extends Controller
{
    public function __invoke(Request $request, Subscriber $subscriber): View
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
}
