<?php

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

    /** @return list<PublishStatus> */
    protected static function publishingStatuses(): array
    {
        return [PublishStatus::Published, PublishStatus::Scheduled];
    }
}
