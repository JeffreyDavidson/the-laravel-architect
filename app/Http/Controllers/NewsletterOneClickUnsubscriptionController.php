<?php

namespace App\Http\Controllers;

use App\Actions\UnsubscribeFromNewsletter;
use App\Models\Subscriber;
use Illuminate\Http\Response;

/**
 * Handles RFC 8058 one-click unsubscribe requests that mail providers send
 * to the List-Unsubscribe URL. Providers post server-to-server without a
 * session or forgery token, so the signed URL is the only credential.
 */
class NewsletterOneClickUnsubscriptionController
{
    public function __invoke(Subscriber $subscriber, UnsubscribeFromNewsletter $unsubscribeFromNewsletter): Response
    {
        $unsubscribeFromNewsletter->handle($subscriber);

        return response()->noContent();
    }
}
