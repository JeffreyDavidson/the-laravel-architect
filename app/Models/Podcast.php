<?php

namespace App\Models;

use App\Models\Concerns\DeletesOwnedContent;
use App\Models\Concerns\ManagesStoredMedia;
use App\Models\Concerns\TracksActivity;
use App\Observers\PodcastObserver;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('name', 'slug', 'description', 'long_description', 'cover_image_path', 'color', 'apple_url', 'spotify_url', 'rss_url', 'youtube_url', 'is_active', 'sort_order')]
#[ObservedBy(PodcastObserver::class)]
#[Sluggable(from: 'name')]
/** @property-read Collection<int, Episode> $publishedEpisodes */
class Podcast extends Model
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

    private const string DEFAULT_COLOR = '#6366f1';

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

    /** @return Attribute<string|null, never> */
    protected function coverImageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->cover_image_path) {
                return Storage::disk('public')
                    ->url(
                        $this->cover_image_path,
                    );
            }

            $resources = $this->fallbackCoverImageResources();

            return $resources ? Vite::asset($resources[512]) : null;
        });
    }

    /** @return Attribute<non-falsy-string|null, never> */
    protected function fallbackCoverImageSrcset(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->cover_image_path) {
                return null;
            }

            $resources = $this->fallbackCoverImageResources();

            if (! $resources) {
                return null;
            }

            $srcset = [];

            foreach ($resources as $width => $resource) {
                $srcset[] = Vite::asset($resource)." {$width}w";
            }

            return implode(', ', $srcset);
        });
    }

    /** @return Attribute<non-falsy-string, never> */
    protected function displayColor(): Attribute
    {
        return Attribute::get(fn (): string => is_string($this->color)
            && preg_match('/\A#[0-9a-fA-F]{6}\z/', $this->color) === 1
            ? $this->color
            : self::DEFAULT_COLOR);
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

    /** @return array<int, string>|null */
    private function fallbackCoverImageResources(): ?array
    {
        $artwork = config()->array('podcasts.fallback_artwork')[$this->slug] ?? null;

        if (! is_array($artwork)) {
            return null;
        }

        $resources = [];

        foreach ($artwork as $width => $resource) {
            if (is_int($width) && is_string($resource)) {
                $resources[$width] = $resource;
            }
        }

        return $resources ?: null;
    }
}
