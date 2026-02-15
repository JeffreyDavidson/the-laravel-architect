<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;

class Episode extends Model implements HasMedia
{
    use HasSEO;
    use HasTags;
    use InteractsWithMedia;

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Episode $episode) {
            if (empty($episode->slug)) {
                $episode->slug = Str::slug($episode->title);
            }
        });
    }

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_minutes) return '';
        $hours = intdiv($this->duration_minutes, 60);
        $mins = $this->duration_minutes % 60;
        return $hours > 0 ? "{$hours}h {$mins}m" : "{$mins} min";
    }

    public function getEpisodeCodeAttribute(): string
    {
        return 'S' . str_pad($this->season_number, 2, '0', STR_PAD_LEFT)
            . 'E' . str_pad($this->episode_number, 2, '0', STR_PAD_LEFT);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')->singleFile();
        $this->addMediaCollection('audio')->singleFile();
    }

    public function getDynamicSEOData(): SEOData
    {
        $podcast = $this->podcast;

        return new SEOData(
            title: $this->title . ' — ' . ($podcast?->name ?? 'Podcast'),
            description: $this->description,
        );
    }
}
