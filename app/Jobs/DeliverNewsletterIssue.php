<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use App\Support\Newsletter\UnsubscribeUrlGenerator;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Mail;

/**
 * Sends one newsletter delivery, dropping it instead when the issue is no longer
 * published or the subscriber is no longer active, so nobody is emailed a link
 * that would 404. Rate-limited releases are retried until the
 * deadline, while genuine failures stop after a few exceptions. The email's
 * idempotency key stops a retry from resending when the send succeeded but
 * recording it did not, and the timeout ends a stuck send before the database
 * queue's retry_after hands the job to another worker.
 */
#[DeleteWhenMissingModels]
#[MaxExceptions(3)]
#[Timeout(60)]
#[Backoff([60, 300, 900])]
final class DeliverNewsletterIssue implements ShouldQueue
{
    use Queueable;

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

        if (
            ! $issue instanceof NewsletterIssue
            || ! $issue->isPublished()
            || ! $subscriber instanceof Subscriber
            || ! $subscriber->isActive()
        ) {
            $delivery->delete();

            return;
        }

        Mail::to($subscriber->email)
            ->send(new NewsletterIssueMail(
                $issue,
                $unsubscribeUrlGenerator->for($subscriber),
                $delivery,
            ));

        $delivery->update(['sent_at' => now()]);
    }
}
