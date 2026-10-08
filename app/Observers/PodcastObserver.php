<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Episode;
use App\Models\Podcast;
use App\Services\StoredMediaLifecycle;
use Closure;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/**
 * Keeps the podcast cover in step with the row and cascades trash, restore and force
 * delete to the podcast's episodes. Podcast::delete() runs inside a transaction, so a
 * failed episode delete rolls the whole podcast delete back.
 */
final readonly class PodcastObserver
{
    public function __construct(private StoredMediaLifecycle $media) {}

    public function created(Podcast $podcast): void
    {
        $this->media->created($podcast, 'cover_image_path', 'podcast');
    }

    public function updated(Podcast $podcast): void
    {
        $this->media->updated($podcast, 'cover_image_path', 'podcast');
    }

    /**
     * Force delete every episode, trashed or not, with its own cleanup before the podcast
     * row goes (the database foreign key would otherwise remove the rows without it).
     */
    public function deleting(Podcast $podcast): void
    {
        if (! $podcast->isForceDeleting()) {
            return;
        }

        $this->deleteEpisodes(
            $podcast->episodes()
                ->withTrashed(),
            fn (Episode $episode): ?bool => $episode->forceDelete(),
        );
    }

    /**
     * Trash the podcast's episodes once the podcast itself is trashed, then stamp them with
     * the podcast's deleted_at so a delete that spans several seconds still lets restoring()
     * bring them all back.
     */
    public function trashed(Podcast $podcast): void
    {
        $trashedEpisodeIds = $this->deleteEpisodes(
            $podcast->episodes(),
            fn (Episode $episode): ?bool => $episode->delete(),
        );

        if ($trashedEpisodeIds === []) {
            return;
        }

        $podcast->episodes()
            ->onlyTrashed()
            ->whereKey($trashedEpisodeIds)
            ->toBase()
            ->update([
                'deleted_at' => $podcast->fromDateTime($podcast->getAttribute($podcast->getDeletedAtColumn())),
            ]);
    }

    /**
     * Restore the episodes that were trashed together with the podcast, leaving episodes
     * that were trashed separately before it in the trash.
     */
    public function restoring(Podcast $podcast): void
    {
        $deletedAt = $podcast->getAttribute('deleted_at');

        if ($deletedAt === null) {
            return;
        }

        $podcast->episodes()
            ->onlyTrashed()
            ->where('deleted_at', '>=', $deletedAt)
            ->each(fn (Episode $episode): bool => $episode->restore());
    }

    public function forceDeleted(Podcast $podcast): void
    {
        $this->media->forceDeleted($podcast, 'cover_image_path', 'podcast');
    }

    /**
     * @param  HasMany<Episode, Podcast>  $episodes
     * @param  Closure(Episode): ?bool  $delete
     * @return list<int>
     */
    private function deleteEpisodes(HasMany $episodes, Closure $delete): array
    {
        $deletedEpisodeIds = [];

        foreach ($episodes->lazyById() as $episode) {
            if ($delete($episode) !== true) {
                throw new RuntimeException('Podcast deletion was cancelled because an episode could not be deleted.');
            }

            $deletedEpisodeIds[] = $episode->id;
        }

        return $deletedEpisodeIds;
    }
}
