<?php

namespace App\Jobs;

use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use App\Support\Newsletter\UnsubscribeUrlGenerator;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Mail;

/**
 * Sends one newsletter delivery. Rate-limited releases are retried until the
 * deadline, while genuine failures stop after a few exceptions.
 */
class DeliverNewsletterIssue implements ShouldQueue
{
    use Queueable;

    public bool $deleteWhenMissingModels = true;

    public int $maxExceptions = 3;

    /** @var list<int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public NewsletterDelivery $delivery) {}

    /** @return list<object> */
    public function middleware(): array
    {
        return [new RateLimited('newsletter-delivery')];
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addDay();
    }

    public function handle(UnsubscribeUrlGenerator $unsubscribeUrlGenerator): void
    {
        $delivery = $this->delivery;

        if ($delivery->sent_at !== null) {
            return;
        }

        $delivery->loadMissing(['newsletterIssue', 'subscriber']);
        $issue = $delivery->newsletterIssue;
        $subscriber = $delivery->subscriber;

        if (! $issue instanceof NewsletterIssue || ! $subscriber instanceof Subscriber || ! $subscriber->isActive()) {
            $delivery->delete();

            return;
        }

        Mail::to($subscriber->email)
            ->send(new NewsletterIssueMail(
                $issue,
                $unsubscribeUrlGenerator->for($subscriber),
            ));

        $delivery->update(['sent_at' => now()]);
    }
}
