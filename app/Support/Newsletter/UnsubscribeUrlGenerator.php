<?php

namespace App\Support\Newsletter;

use App\Models\Subscriber;
use Illuminate\Support\Facades\URL;

final class UnsubscribeUrlGenerator
{
    /**
     * Newsletter links must keep working for as long as the email can be
     * read, so they do not expire. The signature still binds the link to one
     * subscriber, and the same URL accepts one-click unsubscribe POSTs.
     */
    public function for(Subscriber $subscriber): string
    {
        return URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $subscriber]);
    }
}
