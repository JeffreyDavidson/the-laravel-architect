<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\MediaHealthType;
use App\Queries\MediaHealthQuery;
use App\Services\ResponsiveImageVariants;

final readonly class RepairImageVariants
{
    public function __construct(
        private MediaHealthQuery $media,
        private ResponsiveImageVariants $images,
    ) {}

    /**
     * Regenerate the responsive variants of one record's stored image without touching the
     * original. Returns false when the record has no stored image or regeneration fails.
     */
    public function handle(MediaHealthType $type, string $recordKey): bool
    {
        $path = $this->media->sourcePath($type, $recordKey);

        return $path !== null && $this->images->generate($path);
    }
}
