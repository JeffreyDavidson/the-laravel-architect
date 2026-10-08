<?php

declare(strict_types=1);

namespace App\Presenters\Concerns;

use Illuminate\Contracts\Routing\UrlGenerator;

/**
 * The links for publishable content: its public page while it is live, and a signed preview
 * that lets an editor open it before then. The admin "View on site" action uses whichever applies.
 */
trait LinksToPublicPageOrPreview
{
    /** How long a signed preview link keeps working. */
    private const int PREVIEW_LIFETIME_HOURS = 2;

    /**
     * The public page while the content is live, or null while that page would not be reachable
     * (unpublished, scheduled, or an episode of an inactive show).
     */
    abstract public function publicUrl(): ?string;

    /** A signed preview of the content that expires after PREVIEW_LIFETIME_HOURS. */
    abstract public function previewUrl(): string;

    /** The public page when the content is live, otherwise a signed preview. */
    public function publicOrPreviewUrl(): string
    {
        return $this->publicUrl() ?? $this->previewUrl();
    }

    /** @param  array<string, mixed>  $parameters */
    private function signedPreviewUrl(UrlGenerator $urls, string $routeName, array $parameters): string
    {
        return $urls->temporarySignedRoute($routeName, now()->addHours(self::PREVIEW_LIFETIME_HOURS), $parameters);
    }
}
