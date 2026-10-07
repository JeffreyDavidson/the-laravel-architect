<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Publishable;
use App\Enums\PublishStatus;
use App\Models\Attributes\PublishingStatus;
use App\Models\Concerns\DeletesOwnedContent;
use App\Models\Concerns\Featurable;
use App\Models\Concerns\HasFeaturedImage;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\HasTagsUntilForceDeleted;
use App\Models\Concerns\LocksSlugAfterPublication;
use App\Models\Concerns\TracksActivity;
use App\Observers\ProjectObserver;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('title', 'slug', 'description', 'content', 'featured_image_path', 'url', 'github_url', 'tech_stack', 'is_featured', 'sort_order', 'status')]
#[ObservedBy(ProjectObserver::class)]
#[Sluggable(from: 'title')]
#[PublishingStatus(publishedAt: null)]
/**
 * @property array<int, string>|null $tech_stack
 * @property-read string|null $featured_image_url
 */
final class Project extends Model implements Publishable
{
    use DeletesOwnedContent;
    use Featurable;

    /** @use HasFactory<ProjectFactory> */
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
            'tech_stack' => 'array',
            'is_featured' => 'boolean',
            'status' => PublishStatus::class,
            'slug_locked_at' => 'datetime',
        ];
    }

    /**
     * The tech stack with surrounding whitespace removed and blank or non-string
     * entries dropped. ContentReadinessCriteria mirrors this rule in SQL.
     *
     * @return list<non-empty-string>
     */
    public function technologies(): array
    {
        $techStack = $this->tech_stack;

        if (! is_array($techStack)) {
            return [];
        }

        $technologies = [];

        foreach ($techStack as $technology) {
            $technology = is_string($technology) ? trim($technology) : '';

            if ($technology !== '') {
                $technologies[] = $technology;
            }
        }

        return $technologies;
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->title,
            description: $this->description,
            image: $this->featured_image_url,
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('application')
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

    /** @return list<PublishStatus> */
    protected static function publishingStatuses(): array
    {
        return [PublishStatus::Published];
    }
}
