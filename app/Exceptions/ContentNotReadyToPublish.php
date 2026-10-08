<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ReadinessCheck;
use RuntimeException;

/**
 * Thrown by PublishContent when content is missing a required-to-publish detail.
 */
final class ContentNotReadyToPublish extends RuntimeException
{
    /**
     * @param  string  $contentType  The content type as people read it, such as "Newsletter Issue".
     * @param  non-empty-list<ReadinessCheck>  $issues  The required checks the content still fails.
     */
    public function __construct(public readonly string $contentType, public readonly array $issues)
    {
        parent::__construct("{$contentType} is not ready to publish. Missing: {$this->issueSummary(', ')}.");
    }

    /** The labels of the missing details, joined by the separator. */
    public function issueSummary(string $separator): string
    {
        return implode($separator, array_map(fn (ReadinessCheck $issue): string => $issue->getLabel(), $this->issues));
    }
}
