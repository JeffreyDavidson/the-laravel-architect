<?php

namespace App\Jobs;

use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use App\Models\ContactInquiry;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Connection;
use Illuminate\Queue\Attributes\FailOnTimeout;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Sends the owner notification and the sender confirmation for one contact inquiry.
 * Each successful send is stamped on the inquiry, so a retry only sends what is missing,
 * and the payload carries just the inquiry ID. Resend keeps idempotency keys for 24 hours,
 * so older inquiries fail for manual review instead of risking a duplicate.
 */
#[Connection('database')]
#[Tries(3)]
#[Timeout(60)]
#[FailOnTimeout]
#[Backoff([60, 300, 900])]
class SendContactInquiryEmails implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public function __construct(public int $contactInquiryId) {}

    /** @return list<WithoutOverlapping> */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping("contact-emails:{$this->contactInquiryId}")
                ->withPrefix('')
                ->shared()
                ->releaseAfter(60)
                ->expireAfter(120),
        ];
    }

    public function handle(Mailer $mailer): void
    {
        $inquiry = ContactInquiry::query()->find($this->contactInquiryId);

        if ($inquiry === null) {
            return;
        }

        if (! $inquiry->canRetryEmails()) {
            $this->fail(new RuntimeException('Contact delivery requires manual review after 23 hours.'));

            return;
        }

        $inquiry->update(['email_attempted_at' => now()]);

        if ($inquiry->notification_sent_at === null) {
            $this->send($mailer, $inquiry, config()->string('mail.contact_to'), new ContactMessageReceived($inquiry), 'notification_sent_at');
        }

        if ($inquiry->confirmation_sent_at === null) {
            $this->send($mailer, $inquiry, new Address($inquiry->email, $inquiry->name), new ContactMessageConfirmation($inquiry), 'confirmation_sent_at');
        }

        if ($inquiry->notification_sent_at === null || $inquiry->confirmation_sent_at === null) {
            throw new RuntimeException('Contact email delivery is incomplete.');
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Contact email delivery requires manual review.', [
            'contact_inquiry_id' => $this->contactInquiryId,
        ]);
    }

    /**
     * Send one email and stamp it only when the mailer reports it sent. Failures are
     * logged without the inquiry's contents and left unstamped for the next attempt.
     */
    private function send(Mailer $mailer, ContactInquiry $inquiry, string|Address $recipient, Mailable $mail, string $stampColumn): void
    {
        try {
            $sent = $mailer
                ->to($recipient)
                ->send($mail);
        } catch (Throwable $exception) {
            Log::error('Failed to send a contact email.', [
                'contact_inquiry_id' => $inquiry->id,
                'email' => $stampColumn,
                'exception' => $exception::class,
            ]);

            return;
        }

        if ($sent === null) {
            return;
        }

        $inquiry->update([$stampColumn => now()]);
    }
}
