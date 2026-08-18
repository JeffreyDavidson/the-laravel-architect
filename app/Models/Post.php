<?php

namespace App\Models;

use App\Contracts\Publishable;
use App\Enums\PublishStatus;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\ManagesStoredMedia;
use App\Services\OgImageCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Tags\HasTags;

#[Fillable('title', 'slug', 'excerpt', 'content', 'featured_image_path', 'category_id', 'user_id', 'status', 'published_at', 'review_notes', 'reviewed_by', 'reviewed_at')]
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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getReadingTimeAttribute(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->content)) / 250));
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
