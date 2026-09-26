<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Attributes\PublishingStatus;
use App\Models\Concerns\DeletesOwnedContent;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\TracksActivity;
use App\Models\Contracts\Publishable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('title', 'slug', 'excerpt', 'content', 'status', 'published_at', 'sent_at')]
#[Sluggable(from: 'title')]
#[PublishingStatus]
/**
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $sent_at
 */
class NewsletterIssue extends Model implements Publishable
{
    use DeletesOwnedContent;
    use HasPublishingStatus;
    use HasSEO;
    use TracksActivity;

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /** @return HasMany<NewsletterDelivery, $this> */
    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class);
    }

    public function wasSent(): bool
    {
        return $this->sent_at !== null;
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->title,
            description: $this->excerpt,
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('application')
            ->logOnly([
                'title',
                'slug',
                'status',
                'published_at',
                'sent_at',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
