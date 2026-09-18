<?php

namespace App\Support\Newsletter;

use App\Models\Subscriber;
use Illuminate\Support\Facades\URL;

final class UnsubscribeUrlGenerator
{
    public function for(Subscriber $subscriber): string
    {
        return URL::temporarySignedRoute('newsletter.unsubscribe', now()->addDay(), ['subscriber' => $subscriber]);
    }
}
