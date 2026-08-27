<?php

namespace App\Models;

use App\Contracts\Publishable;
use App\Enums\PublishStatus;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\ManagesStoredMedia;
use App\Observers\PostObserver;
use App\Services\OgImageCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Tags\HasTags;

#[Fillable('title', 'slug', 'excerpt', 'content', 'featured_image_path', 'category_id', 'user_id', 'status', 'published_at', 'review_notes', 'reviewed_by', 'reviewed_at')]
#[ObservedBy(PostObserver::class)]
/**
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property-read Category|null $category
 */
class Post extends Model implements Publishable
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
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::deleted(function (Post $post): void {
            app(OgImageCache::class)->forget($post);
        });
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

    public function getReadingTimeAttribute(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->content)) / 250));
    }

    public function publishStatus(): PublishStatus
    {
        $status = $this->getAttribute('status');

        if (! $status instanceof PublishStatus) {
            throw new \UnexpectedValueException('Post status was not cast to PublishStatus.');
        }

        return $status;
    }

    public function publishedAt(): ?Carbon
    {
        $publishedAt = $this->getAttribute('published_at');

        if ($publishedAt !== null && ! $publishedAt instanceof Carbon) {
            throw new \UnexpectedValueException('Post published_at was not cast to Carbon.');
        }

        return $publishedAt;
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image_path
            ? Storage::disk('public')->url($this->featured_image_path)
            : null;
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->title,
            description: $this->excerpt,
            image: $this->featured_image_url,
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
        return ['featured_image_path'];
    }
}
