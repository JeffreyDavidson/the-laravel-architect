<?php

namespace App\Models\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class PublishingStatus
{
    public function __construct(
        public ?string $status = 'status',
        public ?string $publishedAt = 'published_at',
    ) {}
}
