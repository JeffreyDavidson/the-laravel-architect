<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\NewsletterIssue;
use App\Presenters\Concerns\LinksToPublicPageOrPreview;
use Illuminate\Contracts\Routing\UrlGenerator;

final readonly class NewsletterIssuePresenter
{
    use LinksToPublicPageOrPreview;

    public function __construct(
        private NewsletterIssue $issue,
        private UrlGenerator $urls,
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
}
