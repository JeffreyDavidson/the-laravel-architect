<?php

namespace App\Actions;

use App\Mail\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

final class RequestNewsletterSubscription
{
    public function __invoke(string $email): void
    {
        $email = Str::of($email)->trim()->lower()->toString();
        $subscriber = Subscriber::query()->firstOrNew(['email' => $email]);

        if ($subscriber->verified_at && ! $subscriber->unsubscribed_at) {
            return;
        }

        $token = Str::random(64);

        $subscriber->fill([
            'subscribed_at' => now(),
            'verified_at' => null,
            'unsubscribed_at' => null,
        ]);
        $subscriber->verification_token_hash = hash('sha256', $token);
        $subscriber->save();

        $confirmationUrl = URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addDay(),
            ['subscriber' => $subscriber, 'token' => $token],
        );

        Mail::to($subscriber->email)->queue(new ConfirmNewsletterSubscription($confirmationUrl));
    }
}
