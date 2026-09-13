<?php

namespace App\Actions;

use App\Mail\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

final class RequestNewsletterSubscription
{
    private const int CONFIRMATION_COOLDOWN_SECONDS = 900;

    private const int LOCK_SECONDS = 30;

    private const int LOCK_WAIT_SECONDS = 5;

    public function __construct(private readonly Mailer $mailer) {}

    public function handle(string $email): void
    {
        $email = Str::of($email)->trim()->lower()->toString();
        $emailHash = hash('sha256', $email);
        $cooldownKey = "newsletter.confirmation.cooldown.{$emailHash}";
        $lockKey = "newsletter.confirmation.lock.{$emailHash}";

        Cache::lock($lockKey, self::LOCK_SECONDS)->block(self::LOCK_WAIT_SECONDS, function () use ($email, $cooldownKey): void {
            $subscriber = Subscriber::query()->firstOrNew(['email' => $email]);

            if ($subscriber->verified_at && ! $subscriber->unsubscribed_at) {
                return;
            }

            if ($subscriber->verification_token_hash
                && ! $subscriber->verified_at
                && ! $subscriber->unsubscribed_at
                && Cache::has($cooldownKey)) {
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

            try {
                $this->mailer->to($subscriber->email)->queue(new ConfirmNewsletterSubscription($confirmationUrl));
            } catch (Throwable $exception) {
                Cache::forget($cooldownKey);

                throw $exception;
            }

            Cache::put($cooldownKey, true, self::CONFIRMATION_COOLDOWN_SECONDS);
        });
    }
}
