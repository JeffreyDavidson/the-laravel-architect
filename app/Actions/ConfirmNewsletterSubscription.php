<?php

namespace App\Actions;

use App\Models\Subscriber;

final class ConfirmNewsletterSubscription
{
    public function __invoke(Subscriber $subscriber): void
    {
        $subscriber->fill([
            'verified_at' => now(),
            'unsubscribed_at' => null,
        ]);
        $subscriber->verification_token_hash = null;
        $subscriber->save();
    }
}
