<?php

namespace App\Models;

use App\Contracts\Publishable;
use App\Enums\PublishStatus;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\ManagesStoredMedia;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Tags\HasTags;

#[Fillable('podcast_id', 'title', 'slug', 'episode_number', 'season_number', 'description', 'show_notes', 'featured_image_path', 'audio_url', 'audio_path', 'embed_url', 'youtube_url', 'duration_minutes', 'guest_name', 'guest_title', 'guest_url', 'status', 'published_at')]
class Episode extends Model implements Publishable
{
    use HasPublishingStatus;
    use HasSEO;
    use HasTags;
    use LogsActivity;
    use ManagesStoredMedia;

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
        ];
    }

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

    public function getFormattedDurationAttribute(): string
    {
        if (! $this->duration_minutes) {
            return '';
        }
        $hours = intdiv($this->duration_minutes, 60);
        $mins = $this->duration_minutes % 60;

        return $hours > 0 ? "{$hours}h {$mins}m" : "{$mins} min";
    }

    public function getEpisodeCodeAttribute(): string
    {
        return 'S'.str_pad($this->season_number, 2, '0', STR_PAD_LEFT)
            .'E'.str_pad($this->episode_number, 2, '0', STR_PAD_LEFT);
    }

    public function getDynamicSEOData(): SEOData
    {
        $podcast = $this->podcast;

        return new SEOData(
            title: $this->title.' — '.($podcast?->name ?? 'Podcast'),
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
        return ['featured_image_path', 'audio_path'];
    }
}
