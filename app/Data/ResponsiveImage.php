<?php

declare(strict_types=1);

namespace App\Data;

/**
 * An image ready for a `<picture>`: the fallback `src` and, when WebP variants exist, their `srcset`.
 */
final readonly class ResponsiveImage
{
    public function __construct(
        public string $src,
        public ?string $srcset,
    ) {}
}
