<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\ManagesStoredMedia;
use App\Observers\PodcastObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('name', 'slug', 'description', 'long_description', 'cover_image_path', 'color', 'apple_url', 'spotify_url', 'rss_url', 'youtube_url', 'is_active', 'sort_order')]
#[ObservedBy(PodcastObserver::class)]
/** @property-read Collection<int, Episode> $publishedEpisodes */
class Podcast extends Model
{
    private const DEFAULT_COLOR = '#6366f1';

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
        static::creating(function (Podcast $podcast) {
            if (empty($podcast->slug)) {
                $podcast->slug = Str::slug($podcast->name);
            }
        });

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
        return $this->episodes()
            ->where('status', PublishStatus::Published)
            ->where('published_at', '<=', now());
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

    public function getCoverImageUrlAttribute(): ?string
    {
        if ($this->cover_image_path) {
            return Storage::disk('public')->url($this->cover_image_path);
        }

        $resources = $this->fallbackCoverImageResources();

        return $resources
            ? Vite::asset($resources[512])
            : null;
    }

    public function getFallbackCoverImageSrcsetAttribute(): ?string
    {
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
    }

    public function getDisplayColorAttribute(): string
    {
        if (is_string($this->color) && preg_match('/\A#[0-9a-fA-F]{6}\z/', $this->color) === 1) {
            return $this->color;
        }

        return self::DEFAULT_COLOR;
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
        return match ($this->slug) {
            'coffee-with-the-laravel-architect' => [
                128 => 'resources/images/podcast-coffee-logo-128.webp',
                320 => 'resources/images/podcast-coffee-logo-320.webp',
                512 => 'resources/images/podcast-coffee-logo-512.webp',
            ],
            'embracing-cloudy-days' => [
                128 => 'resources/images/podcast-cloudy-logo-128.webp',
                320 => 'resources/images/podcast-cloudy-logo-320.webp',
                512 => 'resources/images/podcast-cloudy-logo-512.webp',
            ],
            default => null,
        };
    }
}
