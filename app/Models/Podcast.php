<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\ManagesStoredMedia;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('name', 'slug', 'description', 'long_description', 'cover_image_path', 'color', 'apple_url', 'spotify_url', 'rss_url', 'youtube_url', 'is_active', 'sort_order')]
class Podcast extends Model
{
    use HasSEO;
    use LogsActivity;
    use ManagesStoredMedia;

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

        // Fallback to static files
        $slug = $this->slug;
        $map = [
            'coffee-with-the-laravel-architect' => '/images/podcast-coffee-logo-512.webp',
            'embracing-cloudy-days' => '/images/podcast-cloudy-logo-512.webp',
        ];

        return $map[$slug] ?? null;
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
            ->logOnlyDirty()
            ->logAll()
            ->dontLogEmptyChanges();
    }

    protected function storedMediaAttributes(): array
    {
        return ['cover_image_path'];
    }
}
