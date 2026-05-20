<?php

namespace App\Models\Concerns;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasPublishingStatus
{
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PublishStatus::Published)
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === PublishStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }
}
