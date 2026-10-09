<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\NewsletterConfirmationMail;
use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\NewsletterMails;
use LogicException;

/**
 * TLA's newsletter emails for creator-kit's newsletter actions and delivery job. Bound to
 * the package's `NewsletterMails` in `AppServiceProvider`; it lives outside `App\Mail`
 * because every class there is a mailable.
 */
final class NewsletterEmails implements NewsletterMails
{
    public function confirmation(string $confirmationUrl): NewsletterConfirmationMail
    {
        return new NewsletterConfirmationMail($confirmationUrl);
    }

    public function issue(Model $issue, ?string $unsubscribeUrl = null, ?Model $delivery = null): NewsletterIssueMail
    {
        if (! $issue instanceof NewsletterIssue || ($delivery instanceof Model && ! $delivery instanceof NewsletterDelivery)) {
            throw new LogicException('TLA\'s newsletter email needs a NewsletterIssue and, for a subscriber\'s copy, its NewsletterDelivery.');
        }

        return new NewsletterIssueMail($issue, $unsubscribeUrl, $delivery);
    }
}
