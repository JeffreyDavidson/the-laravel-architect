<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\MediaHealthStatus;
use App\Enums\MediaHealthType;
use App\Enums\MediaSourceStatus;
use App\Enums\MediaVariantStatus;

/**
 * The health of one record's stored image. The filename is null when the record has no
 * image; the dimensions and file size are null unless the image could be read.
 */
final readonly class MediaHealthRecord
{
    public function __construct(
        public MediaHealthType $type,
        public string $recordKey,
        public string $title,
        public ?string $filename,
        public ?int $width,
        public ?int $height,
        public ?int $fileSize,
        public MediaSourceStatus $sourceStatus,
        public MediaVariantStatus $variantStatus,
        public MediaHealthStatus $status,
        public bool $repairable,
    ) {}

    /** The record's key across every content type, such as "project:12". */
    public function key(): string
    {
        return "{$this->type->value}:{$this->recordKey}";
    }
}
