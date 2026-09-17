<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TracksActivity;

use App\Models\Concerns\Featurable;
use App\Models\Concerns\HasPublicationDate;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;

/** @property Carbon|null $synced_at */
#[Fillable('youtube_id', 'title', 'slug', 'description', 'thumbnail_url', 'duration', 'view_count', 'like_count', 'comment_count', 'is_featured', 'published_at', 'synced_at')]
#[Sluggable(from: 'title')]
class Video extends Model
{
    use TracksActivity;
    use Featurable;
    use HasPublicationDate;

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

    /** @return Attribute<string, never> */
    protected function youtubeUrl(): Attribute
    {
        return Attribute::make(get: fn (): string => "https://www.youtube.com/watch?v={$this->youtube_id}");
    }

    /** @return Attribute<string, never> */
    protected function embedUrl(): Attribute
    {
        return Attribute::make(get: fn (): string => "https://www.youtube.com/embed/{$this->youtube_id}");
    }
}
