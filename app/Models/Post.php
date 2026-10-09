<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SourceReviewStatus;
use App\Models\Concerns\DeletesOwnedContent;
use App\Models\Concerns\HasTagsUntilForceDeleted;
use App\Models\Concerns\TracksActivity;
use App\Observers\PostObserver;
use Carbon\CarbonInterface;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use JeffreyDavidson\CreatorKit\Contracts\Publishable;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Models\Attributes\PublishingStatus;
use JeffreyDavidson\CreatorKit\Models\Concerns\HasPublishingStatus;
use JeffreyDavidson\CreatorKit\Models\Concerns\LocksSlugAfterPublication;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $updated_at
 * @property-read Category|null $category
 */
#[Fillable('title', 'slug', 'excerpt', 'content', 'featured_image_path', 'category_id', 'user_id', 'status', 'published_at', 'review_notes', 'reviewed_by', 'reviewed_at', 'source_url', 'last_reviewed_at')]
#[ObservedBy(PostObserver::class)]
#[Sluggable(from: 'title')]
#[PublishingStatus]
final class Post extends Model implements Publishable
{
    use DeletesOwnedContent;

    /** @use HasFactory<PostFactory> */
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
            'reviewed_at' => 'datetime',
            'last_reviewed_at' => 'date',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Posts whose official source has never been reviewed or was last reviewed longer
     * ago than the configured interval.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function reviewDue(Builder $query): void
    {
        $query->whereNotNull('source_url')
            ->where(function (Builder $query): void {
                $query->whereNull('last_reviewed_at')
                    ->orWhere('last_reviewed_at', '<', $this->reviewCutoff());
            });
    }

    public function isReviewDue(): bool
    {
        $lastReviewedAt = $this->getAttribute('last_reviewed_at');

        return filled($this->getAttribute('source_url'))
            && (! $lastReviewedAt instanceof CarbonInterface || $lastReviewedAt->lt($this->reviewCutoff()));
    }

    public function sourceReviewStatus(): SourceReviewStatus
    {
        if (blank($this->getAttribute('source_url'))) {
            return SourceReviewStatus::NotTracked;
        }

        return $this->isReviewDue()
            ? SourceReviewStatus::ReviewDue
            : SourceReviewStatus::Current;
    }

    private function reviewCutoff(): CarbonInterface
    {
        return today()->subDays(config()->integer('content.post_review_interval_days'));
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsToMany<Episode, $this> */
    public function episodes(): BelongsToMany
    {
        return $this->belongsToMany(Episode::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('application')
            ->logOnly([
                'title',
                'slug',
                'featured_image_path',
                'category_id',
                'user_id',
                'status',
                'published_at',
                'reviewed_by',
                'reviewed_at',
                'source_url',
                'last_reviewed_at',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
