<?php

namespace App\Mail;

use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Str;

/**
 * A newsletter issue for one recipient. Delivery jobs send it immediately,
 * so it is not queued itself. A null unsubscribe URL marks an editor's test
 * email, which omits the one-click unsubscribe headers. A subscriber's copy
 * carries its delivery, whose stable Resend idempotency key stops a retried
 * job from emailing the same subscriber twice; test emails carry none, so an
 * editor can resend one after changing the issue.
 */
class NewsletterIssueMail extends Mailable
{
    public function __construct(
        public readonly NewsletterIssue $issue,
        public readonly ?string $unsubscribeUrl = null,
        public readonly ?NewsletterDelivery $delivery = null,
    ) {}

    public function envelope(): Envelope
    {
        $issue = $this->issue;

        return new Envelope(subject: $issue->title);
    }

    public function headers(): Headers
    {
        $text = [];

        if ($this->unsubscribeUrl !== null) {
            $text['List-Unsubscribe'] = "<{$this->unsubscribeUrl}>";
            $text['List-Unsubscribe-Post'] = 'List-Unsubscribe=One-Click';
        }

        if ($this->delivery instanceof NewsletterDelivery) {
            $text['Resend-Idempotency-Key'] = "tla-newsletter-delivery-{$this->deliveryFingerprint($this->delivery)}";
        }

        return new Headers(text: $text);
    }

    public function content(): Content
    {
        $issue = $this->issue;

        return new Content(
            html: 'mail.newsletter-issue',
            text: 'mail.newsletter-issue-text',
            with: [
                // Match the public site's Markdown safety settings.
                'bodyHtml' => Str::markdown($issue->content, [
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ]),
                'issueUrl' => route('newsletter.issue', $issue),
            ],
        );
    }

    /**
     * Identify the delivery across environments and database resets, like the
     * contact emails' keys. Resend keeps a key for 24 hours, which covers every
     * attempt because delivery jobs stop retrying a day after dispatch.
     */
    private function deliveryFingerprint(NewsletterDelivery $delivery): string
    {
        return hash('sha256', implode('|', [
            config()->string('app.url'),
            $delivery->id,
            $delivery->created_at?->toISOString(),
        ]));
    }
}
