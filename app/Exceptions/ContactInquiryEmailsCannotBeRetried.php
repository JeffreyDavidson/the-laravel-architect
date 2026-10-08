<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by RetryContactInquiryEmails when nothing is left to retry or the provider's
 * idempotency window has passed.
 */
final class ContactInquiryEmailsCannotBeRetried extends RuntimeException
{
    public static function nothingToRetry(): self
    {
        return new self('Every email for this inquiry was sent, or delivery has not been attempted yet.');
    }

    public static function retryWindowPassed(): self
    {
        return new self('Retries are available for 23 hours after submission. Check this inquiry with the mail provider instead.');
    }
}
