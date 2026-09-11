<?php

namespace App\Models;

use App\Attributes\PublishingStatus;
use App\Enums\PublishStatus;
use App\Models\Concerns\HasFeaturedImage;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\ManagesStoredMedia;
use App\Models\Contracts\Publishable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Tags\HasTags;

#[Fillable('podcast_id', 'title', 'slug', 'episode_number', 'season_number', 'description', 'show_notes', 'featured_image_path', 'audio_url', 'audio_path', 'embed_url', 'youtube_url', 'duration_minutes', 'guest_name', 'guest_title', 'guest_url', 'status', 'published_at')]
#[Sluggable(from: 'title')]
#[PublishingStatus]
/**
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property-read string|null $featured_image_url
 * @property-read Podcast|null $podcast
 */
class Episode extends Model implements Publishable
{
    use HasFeaturedImage;
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

    /** @return BelongsTo<Podcast, $this> */
    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    public function getDynamicSEOData(): SEOData
    {
        $podcast = $this->getRelationValue('podcast');
        $podcastName = $podcast instanceof Podcast ? $podcast->name : 'Podcast';

        return new SEOData(
            title: $this->title.' — '.$podcastName,
            description: $this->description,
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'podcast_id',
                'title',
                'slug',
                'episode_number',
                'season_number',
                'featured_image_path',
                'audio_url',
                'audio_path',
                'embed_url',
                'youtube_url',
                'duration_minutes',
                'status',
                'published_at',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected function storedMediaAttributes(): array
    {
        return ['featured_image_path', 'audio_path'];
    }
}
