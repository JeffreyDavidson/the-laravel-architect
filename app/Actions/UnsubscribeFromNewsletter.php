<?php

namespace App\Actions;

use App\Models\Subscriber;

final class UnsubscribeFromNewsletter
{
    public function __invoke(Subscriber $subscriber): void
    {
        $subscriber->fill([
            'unsubscribed_at' => now(),
        ]);
        $subscriber->verification_token_hash = null;
        $subscriber->save();
    }
}
