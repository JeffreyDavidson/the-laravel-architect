<?php

namespace App\Models;

use App\Models\Concerns\TracksActivity;
use App\Enums\PublishStatus;
use App\Models\Attributes\PublishingStatus;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Contracts\Publishable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

#[Fillable('title', 'slug', 'excerpt', 'content', 'status', 'published_at')]
#[Sluggable(from: 'title')]
#[PublishingStatus]
/**
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 */
class NewsletterIssue extends Model implements Publishable
{
    use TracksActivity;
    use HasPublishingStatus;
    use HasSEO;

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->title,
            description: $this->excerpt,
        );
    }
}
