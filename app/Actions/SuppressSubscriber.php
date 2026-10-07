<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SuppressionReason;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

final class SuppressSubscriber
{
    /**
     * Stops all mail to an address that bounced or complained. Returns false
     * when the address is not a subscriber.
     */
    public function handle(string $email, SuppressionReason $reason): bool
    {
        $subscriber = Subscriber::query()
            ->where('email', Str::of($email)
                ->trim()
                ->lower()
                ->toString())
            ->first();

        if ($subscriber === null) {
            return false;
        }

        if ($subscriber->suppressed_at !== null) {
            return true;
        }

        $subscriber->fill([
            'unsubscribed_at' => $subscriber->unsubscribed_at ?? Date::now(),
            'suppressed_at' => Date::now(),
            'suppression_reason' => $reason,
        ]);
        $subscriber->verification_token_hash = null;
        $subscriber->save();

        return true;
    }
}
