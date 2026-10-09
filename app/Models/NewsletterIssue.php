<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\DeletesOwnedContent;
use App\Models\Concerns\TracksActivity;
use Database\Factories\NewsletterIssueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use JeffreyDavidson\CreatorKit\Contracts\NewsletterIssue as NewsletterIssueContract;
use JeffreyDavidson\CreatorKit\Contracts\Publishable;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Models\Attributes\PublishingStatus;
use JeffreyDavidson\CreatorKit\Models\Concerns\HasPublishingStatus;
use JeffreyDavidson\CreatorKit\Models\Concerns\LocksSlugAfterPublication;
use JeffreyDavidson\CreatorKit\Models\Concerns\SendsAsNewsletter;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable('title', 'slug', 'excerpt', 'content', 'status', 'published_at', 'sent_at')]
#[Sluggable(from: 'title')]
#[PublishingStatus]
/**
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $sent_at
 */
final class NewsletterIssue extends Model implements NewsletterIssueContract, Publishable
{
    use DeletesOwnedContent;

    /** @use HasFactory<NewsletterIssueFactory> */
    use HasFactory;

    use HasPublishingStatus;
    use HasSEO;
    use LocksSlugAfterPublication;
    use SendsAsNewsletter;
    use SoftDeletes;
    use TracksActivity;

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'slug_locked_at' => 'datetime',
            'published_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
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
