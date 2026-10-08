<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by SendNewsletterIssue when the issue is not live yet or was already sent.
 */
final class NewsletterIssueCannotBeSent extends RuntimeException
{
    public static function notPublished(): self
    {
        return new self('Only published newsletter issues can be sent.');
    }

    public static function alreadySent(): self
    {
        return new self('This newsletter issue has already been sent.');
    }
}
