<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait HasPublicationDate
{
    use HasPublishingConfiguration;

    /** @param Builder<static> $query */
    #[Scope]
    protected function published(Builder $query): void
    {
        $column = static::publishingStatusConfiguration()->publishedAt;

        if ($column === null) {
            return;
        }

        $query->whereNotNull($column)->where($column, '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->publicationDateHasArrived();
    }

    public function publishedAt(): ?Carbon
    {
        $column = $this->publicationDateColumn();

        if ($column === null) {
            return null;
        }

        $publishedAt = $this->getAttribute($column);

        if ($publishedAt !== null && ! $publishedAt instanceof Carbon) {
            throw new \UnexpectedValueException(static::class.' published_at was not cast to Carbon.');
        }

        return $publishedAt;
    }

    protected function publicationDateHasArrived(): bool
    {
        if ($this->publicationDateColumn() === null) {
            return true;
        }

        $publishedAt = $this->publishedAt();

        return $publishedAt !== null && $publishedAt <= now();
    }

    private function publicationDateColumn(): ?string
    {
        return static::publishingStatusConfiguration()->publishedAt;
    }
}
