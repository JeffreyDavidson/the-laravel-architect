<?php

namespace App\Mail;

use App\Models\NewsletterIssue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Str;

/**
 * A newsletter issue for one recipient. Delivery jobs send it immediately,
 * so it is not queued itself. A null unsubscribe URL marks an editor's test
 * email, which omits the one-click unsubscribe headers.
 */
class NewsletterIssueMail extends Mailable
{
    public function __construct(
        public readonly NewsletterIssue $issue,
        public readonly ?string $unsubscribeUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        $issue = $this->issue;

        return new Envelope(subject: $issue->title);
    }

    public function headers(): Headers
    {
        if ($this->unsubscribeUrl === null) {
            return new Headers;
        }

        return new Headers(text: [
            'List-Unsubscribe' => "<{$this->unsubscribeUrl}>",
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
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
}
