<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\DeletesOwnedContent;
use App\Models\Concerns\ManagesStoredMedia;
use App\Models\Concerns\TracksActivity;
use App\Observers\PodcastObserver;
use App\Presenters\PodcastPresenter;
use Database\Factories\PodcastFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('name', 'slug', 'description', 'long_description', 'cover_image_path', 'color', 'apple_url', 'spotify_url', 'rss_url', 'youtube_url', 'is_active', 'sort_order')]
#[ObservedBy(PodcastObserver::class)]
#[Sluggable(from: 'name')]
/** @property-read Collection<int, Episode> $publishedEpisodes */
final class Podcast extends Model
{
    use DeletesOwnedContent;

    /** @use HasFactory<PodcastFactory> */
    use HasFactory;

    use HasSEO;
    use ManagesStoredMedia;
    use SoftDeletes {
        performDeleteOnModel as performSoftDeleteOnModel;
    }
    use TracksActivity;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Runs only after the delete is confirmed: trash the episodes with the podcast, or
     * permanently delete all of them (with their own cleanup) before the podcast row goes.
     * Trashed episodes are then stamped with the podcast's deleted_at, so a delete that
     * spans several seconds still lets PodcastObserver::restoring() bring them all back.
     */
    protected function performDeleteOnModel(): void
    {
        $episodes = $this->isForceDeleting()
            ? $this->episodes()
                ->withTrashed()
            : $this->episodes();
        $deletedEpisodeIds = [];

        foreach ($episodes->lazyById() as $episode) {
            $deleted = $this->isForceDeleting()
                ? $episode->forceDelete()
                : $episode->delete();

            if ($deleted !== true) {
                throw new \RuntimeException('Podcast deletion was cancelled because an episode could not be deleted.');
            }

            $deletedEpisodeIds[] = $episode->getKey();
        }

        $this->performSoftDeleteOnModel();

        if ($this->isForceDeleting() || $deletedEpisodeIds === []) {
            return;
        }

        $this->episodes()
            ->onlyTrashed()
            ->whereKey($deletedEpisodeIds)
            ->toBase()
            ->update([
                'deleted_at' => $this->fromDateTime($this->getAttribute($this->getDeletedAtColumn())),
            ]);
    }

    /** @return HasMany<Episode, $this> */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    /** @return HasMany<Episode, $this> */
    public function publishedEpisodes(): HasMany
    {
        return $this
            ->episodes()
            ->published();
    }

    public function latestEpisode(): ?Episode
    {
        return $this
            ->publishedEpisodes()
            ->latest('published_at')
            ->first();
    }

    /** @param Builder<Podcast> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * The cover URL for SEO and the public pages; PodcastPresenter decides between the uploaded
     * cover and the bundled artwork.
     *
     * @return Attribute<string|null, never>
     */
    protected function coverImageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => PodcastPresenter::from($this)->coverImageUrl());
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->name,
            description: $this->description,
            image: $this->cover_image_url,
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('application')
            ->logOnly([
                'name',
                'slug',
                'cover_image_path',
                'color',
                'apple_url',
                'spotify_url',
                'rss_url',
                'youtube_url',
                'is_active',
                'sort_order',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected function storedMediaAttributes(): array
    {
        return ['cover_image_path'];
    }
}
