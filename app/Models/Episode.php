<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\DeletesOwnedContent;
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
use JeffreyDavidson\CreatorKit\Contracts\Publishable;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Models\Attributes\PublishingStatus;
use JeffreyDavidson\CreatorKit\Models\Concerns\HasPublishingStatus;
use JeffreyDavidson\CreatorKit\Models\Concerns\HasTagsUntilForceDeleted;
use JeffreyDavidson\CreatorKit\Models\Concerns\LocksSlugAfterPublication;
use JeffreyDavidson\CreatorKit\Support\Podcasts\TransistorShareUrl;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('podcast_id', 'title', 'slug', 'episode_number', 'season_number', 'description', 'show_notes', 'transcript', 'featured_image_path', 'youtube_url', 'duration_seconds', 'guest_name', 'guest_title', 'guest_url', 'status', 'published_at', 'transistor_url')]
#[ObservedBy(EpisodeObserver::class)]
#[Sluggable(from: 'title')]
#[PublishingStatus]
/**
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $updated_at
 * @property-read Podcast|null $podcast
 */
final class Episode extends Model implements Publishable
{
    use DeletesOwnedContent;

    /** @use HasFactory<EpisodeFactory> */
    use HasFactory;

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
     * The Transistor episode ID from a valid share URL
     * (https://share.transistor.fm/s/{id}), or null for anything else.
     */
    public function transistorEpisodeId(): ?string
    {
        return TransistorShareUrl::episodeId($this->getAttribute('transistor_url'));
    }

    /**
     * Whether the episode has something to play: a valid Transistor share URL
     * or a YouTube link. ContentReadinessCriteria mirrors this rule in SQL.
     */
    public function hasMedia(): bool
    {
        return $this->transistorEpisodeId() !== null
            || filled($this->youtube_url);
    }

    /** @return BelongsTo<Podcast, $this> */
    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
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
