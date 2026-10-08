<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterIssue;
use Illuminate\Contracts\Mail\Mailer;

final readonly class SendNewsletterIssueTestEmail
{
    public function __construct(private Mailer $mailer) {}

    /**
     * Email the issue to the site owner without recording a delivery.
     *
     * @return string The address the test email was sent to.
     */
    public function handle(NewsletterIssue $issue): string
    {
        $recipient = config()->string('mail.contact_to');

        $this->mailer
            ->to($recipient)
            ->send(new NewsletterIssueMail($issue));

        return $recipient;
    }
}
