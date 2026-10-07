<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\DeletesOwnedContent;
use App\Models\Concerns\TracksActivity;
use App\Observers\PodcastObserver;
use App\Presenters\PodcastPresenter;
use Database\Factories\PodcastFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('name', 'slug', 'description', 'long_description', 'cover_image_path', 'color', 'apple_url', 'spotify_url', 'rss_url', 'youtube_url', 'is_active', 'sort_order')]
#[ObservedBy(PodcastObserver::class)]
#[Sluggable(from: 'name')]
/** @property-read Collection<int, Episode> $publishedEpisodes */
final class Podcast extends Model
{
    use DeletesOwnedContent;

    /** @use HasFactory<PodcastFactory> */
    use HasFactory;

    use HasSEO;
    use SoftDeletes;
    use TracksActivity;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
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

    /**
     * Temporary: the image URL comes from the presenter until page ViewModels own SEO
     * (architecture plan C2), when models drop HasSEO and this method goes.
     */
    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->name,
            description: $this->description,
            image: PodcastPresenter::from($this)->coverImageUrl(),
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
}
