<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasPublishingStatus
{
    use HasPublicationDate;

    /** @param Builder<static> $query */
    #[Scope]
    protected function published(Builder $query): void
    {
        $configuration = static::publishingStatusConfiguration();
        if ($configuration->status !== null) {
            $query->whereIn($configuration->status, static::publishingStatuses());
        }

        $publishedAtColumn = static::publishingStatusConfiguration()->publishedAt;

        if ($publishedAtColumn !== null) {
            $query->whereNotNull($publishedAtColumn)
                ->where($publishedAtColumn, '<=', now());
        }
    }

    public function isPublished(): bool
    {
        $configuration = static::publishingStatusConfiguration();
        $status = $configuration->status === null
            ? PublishStatus::Published
            : $this->getAttribute($configuration->status);

        return in_array($status, static::publishingStatuses(), true)
            && $this->publicationDateHasArrived();
    }

    public function isScheduled(): bool
    {
        $publishedAt = $this->publishedAt();

        return in_array($this->publishStatus(), static::publishingStatuses(), true)
            && $publishedAt !== null
            && $publishedAt->isFuture();
    }

    /**
     * The publish state change only: it does not check readiness. Domain code
     * publishes through the PublishContent action, which enforces it.
     */
    public function publish(): void
    {
        $configuration = static::publishingStatusConfiguration();

        if ($configuration->publishedAt !== null && $this->publishedAt() === null) {
            $this->setAttribute($configuration->publishedAt, now());
        }

        $publishedAt = $this->publishedAt();
        $status = $publishedAt?->isFuture() === true
            ? PublishStatus::Scheduled
            : PublishStatus::Published;

        $this->setAttribute($this->publishStatusColumn(), $status);
        $this->save();
    }

    public function unpublish(): void
    {
        $this->setAttribute($this->publishStatusColumn(), PublishStatus::Draft);
        $this->save();
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function scheduled(Builder $query): void
    {
        $configuration = static::publishingStatusConfiguration();

        if ($configuration->publishedAt === null) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($configuration->status !== null) {
            $query->whereIn($configuration->status, static::publishingStatuses());
        }

        $query->where($configuration->publishedAt, '>', now());
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function unpublished(Builder $query): void
    {
        $query->whereNot(fn (Builder $query): Builder => $query->published());
    }

    public function publishStatus(): PublishStatus
    {
        $configuration = $this->publishingStatusConfiguration();
        $status = $configuration->status === null
            ? null
            : $this->getAttribute($configuration->status);

        if (! $status instanceof PublishStatus) {
            throw new \UnexpectedValueException(static::class.' status was not cast to PublishStatus.');
        }

        return $status;
    }

    private function publishStatusColumn(): string
    {
        return static::publishingStatusConfiguration()->status
            ?? throw new \UnexpectedValueException(static::class.' has no publishing status column.');
    }

    /** @return list<PublishStatus> */
    protected static function publishingStatuses(): array
    {
        return [PublishStatus::Published, PublishStatus::Scheduled];
    }
}
