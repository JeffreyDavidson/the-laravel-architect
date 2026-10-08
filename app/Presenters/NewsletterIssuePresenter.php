<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\NewsletterIssue;
use App\Presenters\Concerns\LinksToPublicPageOrPreview;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Str;

final readonly class NewsletterIssuePresenter
{
    use LinksToPublicPageOrPreview;

    public function __construct(
        private NewsletterIssue $issue,
        private UrlGenerator $urls,
        private Config $config,
    ) {}

    public static function from(NewsletterIssue $issue): self
    {
        return app()->make(self::class, ['issue' => $issue]);
    }

    public function publicUrl(): ?string
    {
        if (! $this->issue->isPublished()) {
            return null;
        }

        return $this->urls->route('newsletter.issue', $this->issue);
    }

    public function previewUrl(): string
    {
        return $this->signedPreviewUrl($this->urls, 'preview.newsletterIssue', ['newsletterIssue' => $this->issue]);
    }

    /**
     * The issue body for an email's HTML part, rendered with the public site's
     * Markdown safety settings.
     */
    public function emailBodyHtml(): string
    {
        return $this->absoluteHtmlUrls(Str::markdown($this->issue->content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]));
    }

    /** The issue body for an email's plain-text part: the raw Markdown. */
    public function emailBodyText(): string
    {
        return $this->absoluteMarkdownUrls($this->issue->content);
    }

    /** The inbox preview line: the excerpt, or the title without one. */
    public function emailPreheader(): string
    {
        return $this->issue->excerpt ?: $this->issue->title;
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
     * Prefix only relative URLs with `app.url`; absolute, protocol-relative,
     * mailto, tel and anchor URLs are left alone.
     */
    private function absoluteUrl(string $url): string
    {
        if ($url === '' || str_starts_with($url, '#') || str_starts_with($url, '//')) {
            return $url;
        }

        if (preg_match('/^[a-z][a-z0-9+.\-]*:/i', $url) === 1) {
            return $url;
        }

        $base = rtrim($this->config->string('app.url'), '/');

        return str_starts_with($url, '/')
            ? "{$base}{$url}"
            : "{$base}/{$url}";
    }
}
