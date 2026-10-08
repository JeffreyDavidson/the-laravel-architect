<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Featurable;
use App\Models\Concerns\HasPublicationDate;
use App\Models\Concerns\TracksActivity;
use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;
use Spatie\Activitylog\Support\LogOptions;

/** @property Carbon|null $synced_at */
#[Fillable('youtube_id', 'title', 'slug', 'description', 'thumbnail_url', 'duration', 'view_count', 'like_count', 'comment_count', 'is_featured', 'published_at', 'synced_at')]
#[Sluggable(from: 'title')]
final class Video extends Model
{
    use Featurable;

    /** @use HasFactory<VideoFactory> */
    use HasFactory;

    use HasPublicationDate;
    use TracksActivity;

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'synced_at' => 'datetime',
            'view_count' => 'integer',
            'like_count' => 'integer',
            'comment_count' => 'integer',
        ];
    }

    /**
     * Synchronized YouTube statistics and descriptions change outside the
     * editor, so only editorial attributes are recorded.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('application')
            ->logOnly([
                'youtube_id',
                'title',
                'slug',
                'thumbnail_url',
                'duration',
                'is_featured',
                'published_at',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
