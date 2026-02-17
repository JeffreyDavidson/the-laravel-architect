<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Podcast extends Model implements HasMedia
{
    use HasSEO;
    use InteractsWithMedia;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (Podcast $podcast) {
            if (empty($podcast->slug)) {
                $podcast->slug = Str::slug($podcast->name);
            }
        });
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function publishedEpisodes(): HasMany
    {
        return $this->episodes()
            ->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function latestEpisode()
    {
        return $this->publishedEpisodes()->latest('published_at')->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if ($this->hasMedia('cover_image')) {
            return $this->getFirstMediaUrl('cover_image');
        }

        // Fallback to static files
        $slug = $this->slug;
        $map = [
            'coffee-with-the-laravel-architect' => '/images/podcast-coffee-logo.png',
            'embracing-cloudy-days' => '/images/podcast-cloudy-logo.png',
        ];

        return $map[$slug] ?? null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover_image')->singleFile();
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
            ->logFillable();
    }
}
