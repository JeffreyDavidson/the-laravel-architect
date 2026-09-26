<?php

namespace App\Actions;

use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterIssue;
use Illuminate\Support\Facades\Mail;

final class SendNewsletterIssueTestEmail
{
    /**
     * Email the issue to the site owner without recording a delivery.
     *
     * @return string The address the test email was sent to.
     */
    public function handle(NewsletterIssue $issue): string
    {
        $recipient = config()->string('mail.contact_to');

        Mail::to($recipient)->send(new NewsletterIssueMail($issue));

        return $recipient;
    }
}
