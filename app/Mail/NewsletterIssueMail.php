<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Presenters\NewsletterIssuePresenter;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use JeffreyDavidson\CreatorKit\Support\Mail\IdempotencyFingerprint;

/**
 * A newsletter issue for one recipient. Delivery jobs send it immediately,
 * so it is not queued itself. A null unsubscribe URL marks an editor's test
 * email, which omits the one-click unsubscribe headers. A subscriber's copy
 * carries its delivery, whose stable Resend idempotency key stops a retried
 * job from emailing the same subscriber twice; test emails carry none, so an
 * editor can resend one after changing the issue. Resend keeps a key for 24
 * hours, which covers every attempt because delivery jobs stop retrying a day
 * after dispatch. NewsletterIssuePresenter renders the body.
 */
final class NewsletterIssueMail extends Mailable
{
    public function __construct(
        public readonly NewsletterIssue $issue,
        public readonly ?string $unsubscribeUrl = null,
        public readonly ?NewsletterDelivery $delivery = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->issue->title);
    }

    public function headers(): Headers
    {
        $text = [];

        if ($this->unsubscribeUrl !== null) {
            $text['List-Unsubscribe'] = "<{$this->unsubscribeUrl}>";
            $text['List-Unsubscribe-Post'] = 'List-Unsubscribe=One-Click';
        }

        if ($this->delivery instanceof NewsletterDelivery) {
            $fingerprint = IdempotencyFingerprint::for($this->delivery->id, $this->delivery->created_at);

            $text['Resend-Idempotency-Key'] = "tla-newsletter-delivery-{$fingerprint}";
        }

        return new Headers(text: $text);
    }

    public function content(): Content
    {
        $presenter = NewsletterIssuePresenter::from($this->issue);

        return new Content(
            html: 'mail.newsletter-issue',
            text: 'mail.newsletter-issue-text',
            with: [
                'bodyHtml' => $presenter->emailBodyHtml(),
                'bodyText' => $presenter->emailBodyText(),
                'issueUrl' => route('newsletter.issue', $this->issue),
                'preheader' => $presenter->emailPreheader(),
            ],
        );
    }
}
