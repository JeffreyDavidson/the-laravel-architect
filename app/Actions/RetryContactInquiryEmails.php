<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\ContactInquiryEmailsCannotBeRetried;
use App\Jobs\SendContactInquiryEmails;
use App\Models\ContactInquiry;

/**
 * Queues the contact inquiry's emails again after an incomplete delivery attempt. The job
 * only sends the emails without a recorded successful send, and only while the provider
 * still honours the idempotency keys, so a retry cannot deliver a duplicate.
 */
final class RetryContactInquiryEmails
{
    public function handle(ContactInquiry $inquiry): void
    {
        if (! $this->hasUnsentEmails($inquiry)) {
            throw ContactInquiryEmailsCannotBeRetried::nothingToRetry();
        }

        if (! $inquiry->canRetryEmails()) {
            throw ContactInquiryEmailsCannotBeRetried::retryWindowPassed();
        }

        dispatch(new SendContactInquiryEmails($inquiry->id));
    }

    /** Whether delivery was attempted and at least one of the two emails is still unsent. */
    public function hasUnsentEmails(ContactInquiry $inquiry): bool
    {
        return $inquiry->email_attempted_at !== null
            && ($inquiry->notification_sent_at === null || $inquiry->confirmation_sent_at === null);
    }
}
