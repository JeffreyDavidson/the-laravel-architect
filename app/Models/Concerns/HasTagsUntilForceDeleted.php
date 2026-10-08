<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Spatie\Tags\HasTags;

/**
 * spatie/laravel-tags detaches tags on every delete, which would strip them from
 * soft-deleted content before it could be restored. This keeps the package's tag
 * behavior but only detaches tags when the model is force deleted.
 */
trait HasTagsUntilForceDeleted
{
    use HasTags;

    public static function bootHasTags(): void
    {
        static::created(function (self $taggableModel): void {
            if (count($taggableModel->queuedTags) === 0) {
                return;
            }

            $taggableModel->attachTags($taggableModel->queuedTags);

            $taggableModel->queuedTags = [];
        });

        static::forceDeleted(function (self $deletedModel): void {
            $tags = $deletedModel->tags()
                ->get();

            $deletedModel->detachTags($tags);
        });
    }
}
