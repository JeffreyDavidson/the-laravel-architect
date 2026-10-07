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
     * Publish the content, keeping an existing publish date and otherwise using now.
     * This is the state change only; readiness is enforced by the action that calls it.
     */
    public function publish(): void;

    /** Return the content to draft, keeping its publish date and permalink. */
    public function unpublish(): void;
}
