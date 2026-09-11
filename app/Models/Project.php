<?php

namespace App\Models;

use App\Attributes\PublishingStatus;
use App\Enums\PublishStatus;
use App\Models\Concerns\Featurable;
use App\Models\Concerns\HasFeaturedImage;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\ManagesStoredMedia;
use App\Models\Contracts\Publishable;
use App\Observers\ProjectObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Tags\HasTags;

#[Fillable('title', 'slug', 'description', 'content', 'featured_image_path', 'url', 'github_url', 'tech_stack', 'is_featured', 'sort_order', 'status')]
#[ObservedBy(ProjectObserver::class)]
#[Sluggable(from: 'title')]
#[PublishingStatus(publishedAt: null)]
/** @property-read string|null $featured_image_url */
class Project extends Model implements Publishable
{
    use Featurable;
    use HasFeaturedImage;
    use HasPublishingStatus;
    use HasSEO;
    use HasTags;
    use LogsActivity;
    use ManagesStoredMedia;

    protected function casts(): array
    {
        return [
            'tech_stack' => 'array',
            'is_featured' => 'boolean',
            'status' => PublishStatus::class,
        ];
    }

    /** @param Builder<Project> $query */
    #[Scope]
    protected function portfolio(Builder $query): void
    {
        $query->where('slug', '!=', 'the-laravel-architect');
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->title,
            description: $this->description,
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'slug',
                'featured_image_path',
                'url',
                'github_url',
                'tech_stack',
                'is_featured',
                'sort_order',
                'status',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected function storedMediaAttributes(): array
    {
        return ['featured_image_path'];
    }
}
