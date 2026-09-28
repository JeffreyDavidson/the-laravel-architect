<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Content that goes live through the shared Publish / Unpublish admin actions.
 */
interface Publishable
{
    /** Whether the content is live on the public site right now. */
    public function isPublished(): bool;

    /** Whether the content is set to go live at a future publish date. */
    public function isScheduled(): bool;

    /**
     * The required details still missing before the content can be published.
     *
     * @return list<string>
     */
    public function publishingIssues(): array;

    /** Publish the content, keeping an existing publish date and otherwise using now. */
    public function publish(): void;

    /** Return the content to draft, keeping its publish date and permalink. */
    public function unpublish(): void;
}
