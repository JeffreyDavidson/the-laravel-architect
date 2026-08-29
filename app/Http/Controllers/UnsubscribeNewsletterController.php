<?php

namespace App\Http\Controllers;

use App\Actions\UnsubscribeFromNewsletter;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;

class UnsubscribeNewsletterController extends Controller
{
    public function __invoke(
        Subscriber $subscriber,
        UnsubscribeFromNewsletter $unsubscribeFromNewsletter,
    ): RedirectResponse {
        $unsubscribeFromNewsletter($subscriber);

        return redirect()->route('home')->with('newsletter_success', 'You have been unsubscribed.');
    }
}
