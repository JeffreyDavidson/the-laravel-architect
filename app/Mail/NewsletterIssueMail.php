<?php

declare(strict_types=1);

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
final class NewsletterIssueMail extends Mailable
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
                'bodyHtml' => $this->absoluteHtmlUrls(Str::markdown($issue->content, [
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ])),
                'bodyText' => $this->absoluteMarkdownUrls($issue->content),
                'issueUrl' => route('newsletter.issue', $issue),
                'preheader' => $issue->excerpt ?: $issue->title,
            ],
        );
    }

    /**
     * Mail clients cannot resolve relative URLs, so point every relative
     * link and image in the rendered body at the site.
     */
    private function absoluteHtmlUrls(string $html): string
    {
        return preg_replace_callback(
            '/\b(href|src)="([^"]*)"/i',
            fn (array $match): string => "{$match[1]}=\"{$this->absoluteUrl($match[2])}\"",
            $html,
        ) ?? $html;
    }

    /** The plain-text part is the raw Markdown, so rewrite its link and image destinations. */
    private function absoluteMarkdownUrls(string $markdown): string
    {
        return preg_replace_callback(
            '/(\]\(\s*<?)([^)\s>]+)/',
            fn (array $match): string => "{$match[1]}{$this->absoluteUrl($match[2])}",
            $markdown,
        ) ?? $markdown;
    }

    /**
     * Prefix only relative URLs; absolute, protocol-relative, mailto, tel
     * and anchor URLs are left alone.
     */
    private function absoluteUrl(string $url): string
    {
        if ($url === '' || str_starts_with($url, '#') || str_starts_with($url, '//')) {
            return $url;
        }

        if (preg_match('/^[a-z][a-z0-9+.\-]*:/i', $url) === 1) {
            return $url;
        }

        $base = rtrim(config()->string('app.url'), '/');

        return str_starts_with($url, '/')
            ? "{$base}{$url}"
            : "{$base}/{$url}";
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
