<?php

namespace App\Models\Concerns;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasPublishingStatus
{
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', PublishStatus::Published)
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        $publishedAt = $this->getAttribute('published_at');

        return $this->getAttribute('status') === PublishStatus::Published
            && $publishedAt instanceof \DateTimeInterface
            && $publishedAt <= now();
    }
}
