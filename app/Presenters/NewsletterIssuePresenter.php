<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Enums\NewsletterTemplate;
use App\Models\NewsletterIssue;
use App\Presenters\Concerns\LinksToPublicPageOrPreview;
use DOMDocument;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Routing\UrlGenerator;
use JeffreyDavidson\CreatorKit\Services\Media\ResponsiveImageVariants;
use JeffreyDavidson\CreatorKit\Support\Markdown\MarkdownRenderer;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

final readonly class NewsletterIssuePresenter
{
    use LinksToPublicPageOrPreview;

    public function __construct(
        private NewsletterIssue $issue,
        private UrlGenerator $urls,
        private Config $config,
        private MarkdownRenderer $markdown,
        private CssToInlineStyles $styles,
        private ResponsiveImageVariants $images,
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
        $html = $this->absoluteHtmlUrls($this->markdown->safe($this->issue->content));

        if ($this->issue->email_template === null) {
            return $html;
        }

        $document = new DOMDocument;
        $inlined = $this->styles->convert('<div class="newsletter-content">'.$html.'</div>',
            '.newsletter-content{font-size:17px;line-height:1.65;color:#1f2328}.newsletter-content p{margin:0 0 18px}.newsletter-content a{color:#245b91;text-decoration:underline}.newsletter-content h2,.newsletter-content h3{font-size:22px;line-height:1.3;margin:26px 0 12px}.newsletter-content ul,.newsletter-content ol{padding-left:22px;margin:0 0 22px}.newsletter-content li{margin-bottom:8px}.newsletter-content blockquote{margin:22px 0;padding:22px 24px;background:#eaf3fa}.newsletter-content blockquote p{margin:0 0 8px;font-size:20px;line-height:1.45}.newsletter-content blockquote p:first-child{font-size:14px;line-height:20px}.newsletter-content img{max-width:100%;height:auto}.newsletter-content pre{white-space:pre-wrap;overflow-wrap:anywhere;font-size:14px}.newsletter-content code{overflow-wrap:anywhere}'.($this->issue->getAttribute('email_template') === NewsletterTemplate::RelatedReading ? '.newsletter-content h2,.newsletter-content h3{border-top:1px solid #d0d7de;padding-top:24px;margin-top:26px}' : '')
        );
        // The inliner's fragment has no charset declaration for DOMDocument to discover.
        $document->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.$inlined, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $body = $document->getElementsByTagName('body')
            ->item(0);

        if ($body === null) {
            return $html;
        }

        $result = '';

        foreach ($body->childNodes as $node) {
            $result .= $document->saveHTML($node) ?: '';
        }

        return $result;
    }

    /** The issue body for an email's plain-text part: the raw Markdown. */
    public function emailBodyText(): string
    {
        $text = $this->absoluteMarkdownUrls($this->issue->content);

        if ($this->issue->email_template === null) {
            return $text;
        }

        $details = $this->emailDetails();
        $text = ($details['deck'] !== null ? $details['deck']."\n\n" : '').$text;

        if ($details['articleUrl'] !== null) {
            $text .= "\n\n{$details['buttonLabel']}: {$details['articleUrl']}";
        }

        if ($details['heroUrl'] !== null) {
            $text .= "\n\n{$details['heroAlt']}: {$details['heroUrl']}";
        }

        return $text;
    }

    /** The inbox preview line: the excerpt, or the title without one. */
    public function emailPreheader(): string
    {
        return $this->issue->excerpt ?: $this->issue->title;
    }

    /**
     * Optional editorial settings for both the email and its web version.
     *
     * @return array{deck: ?string, topic: ?string, heroUrl: ?string, heroAlt: string, articleUrl: ?string, buttonLabel: string, mastheadUrl: string}
     */
    public function emailDetails(): array
    {
        $article = $this->option('article_url');
        $path = $this->option('hero_path');
        $isArticle = $this->issue->getAttribute('email_template') === NewsletterTemplate::NewArticle;
        $safePath = $path !== null
            && preg_match('~\Anewsletter/[a-zA-Z0-9_./-]+\z~', $path) === 1
            && ! in_array('..', explode('/', $path), true);
        $safeUrl = $article !== null
            && filter_var($article, FILTER_VALIDATE_URL) !== false
            && in_array(strtolower((string) parse_url($article, PHP_URL_SCHEME)), ['http', 'https'], true);

        return [
            'deck' => $this->issue->email_template !== null ? $this->issue->email_deck : null,
            'topic' => $isArticle ? $this->option('topic') : null,
            'heroUrl' => $isArticle && $safePath ? $this->images->url($path) : null,
            'heroAlt' => $this->option('hero_alt') ?? '',
            'articleUrl' => $isArticle && $safeUrl ? $article : null,
            'buttonLabel' => $this->option('button_label') ?? 'Read the article',
            'mastheadUrl' => $this->urls->asset('images/newsletter/masthead.png'),
        ];
    }

    private function option(string $key): ?string
    {
        $options = $this->issue->getAttribute('email_options');

        if (! is_array($options)) {
            return null;
        }

        $value = $options[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
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
            '/(\]\(\s*<?)\s*([^)\s>]+)(?:\s+(?=>))?/',
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
