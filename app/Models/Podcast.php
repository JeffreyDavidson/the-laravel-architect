<?php

namespace App\Models;

use App\Models\Concerns\ManagesStoredMedia;
use App\Observers\PodcastObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('name', 'slug', 'description', 'long_description', 'cover_image_path', 'color', 'apple_url', 'spotify_url', 'rss_url', 'youtube_url', 'is_active', 'sort_order')]
#[ObservedBy(PodcastObserver::class)]
#[Sluggable(from: 'name')]
/** @property-read Collection<int, Episode> $publishedEpisodes */
class Podcast extends Model
{
    private const string DEFAULT_COLOR = '#6366f1';

    use HasSEO;
    use LogsActivity;
    use ManagesStoredMedia;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Podcast $podcast): void {
            $podcast->episodes()->each(
                fn (Episode $episode) => $episode->deleteStoredMediaFiles(),
            );
        });
    }

    /** @return HasMany<Episode, $this> */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    /** @return HasMany<Episode, $this> */
    public function publishedEpisodes(): HasMany
    {
        return $this->episodes()->published();
    }

    public function latestEpisode(): ?Episode
    {
        return $this->publishedEpisodes()->latest('published_at')->first();
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
                return Storage::disk('public')->url($this->cover_image_path);
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
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
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
        $artwork = config('podcasts.fallback_artwork');

        if (! is_array($artwork) || ! is_array($artwork[$this->slug] ?? null)) {
            return null;
        }

        $resources = [];

        foreach ($artwork[$this->slug] as $width => $resource) {
            if (is_int($width) && is_string($resource)) {
                $resources[$width] = $resource;
            }
        }

        return $resources ?: null;
    }
}
