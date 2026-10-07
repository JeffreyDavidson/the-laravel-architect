<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Publishable;
use App\Enums\PublishStatus;
use App\Models\Attributes\PublishingStatus;
use App\Models\Concerns\DeletesOwnedContent;
use App\Models\Concerns\HasFeaturedImage;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\HasTagsUntilForceDeleted;
use App\Models\Concerns\LocksSlugAfterPublication;
use App\Models\Concerns\TracksActivity;
use App\Observers\EpisodeObserver;
use Database\Factories\EpisodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('podcast_id', 'title', 'slug', 'episode_number', 'season_number', 'description', 'show_notes', 'transcript', 'featured_image_path', 'youtube_url', 'duration_seconds', 'guest_name', 'guest_title', 'guest_url', 'status', 'published_at', 'transistor_url')]
#[ObservedBy(EpisodeObserver::class)]
#[Sluggable(from: 'title')]
#[PublishingStatus]
/**
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $updated_at
 * @property-read string|null $featured_image_url
 * @property-read Podcast|null $podcast
 */
final class Episode extends Model implements Publishable
{
    use DeletesOwnedContent;

    /** @use HasFactory<EpisodeFactory> */
    use HasFactory;

    use HasFeaturedImage;
    use HasPublishingStatus;
    use HasSEO;
    use HasTagsUntilForceDeleted;
    use LocksSlugAfterPublication;
    use SoftDeletes;
    use TracksActivity;

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'slug_locked_at' => 'datetime',
            'published_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The Transistor player URL for a valid episode share URL
     * (https://share.transistor.fm/s/{id}), or null for anything else.
     */
    public function transistorEmbedUrl(): ?string
    {
        $url = $this->getAttribute('transistor_url');
        $matches = [];

        if (! is_string($url) || preg_match('/\Ahttps:\/\/share\.transistor\.fm\/s\/([a-zA-Z0-9]+)\/?\z/', $url, $matches) !== 1) {
            return null;
        }

        return "https://share.transistor.fm/e/{$matches[1]}";
    }

    /**
     * Whether the episode has something to play: a valid Transistor share URL
     * or a YouTube link. ContentReadinessCriteria mirrors this rule in SQL.
     */
    public function hasMedia(): bool
    {
        return $this->transistorEmbedUrl() !== null
            || filled($this->youtube_url);
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
            ->useLogName('application')
            ->logOnly([
                'podcast_id',
                'title',
                'slug',
                'episode_number',
                'season_number',
                'featured_image_path',
                'transistor_url',
                'youtube_url',
                'duration_seconds',
                'status',
                'published_at',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
