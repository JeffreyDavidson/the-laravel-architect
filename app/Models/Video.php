<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable('youtube_id', 'title', 'slug', 'description', 'thumbnail_url', 'duration', 'view_count', 'like_count', 'comment_count', 'is_featured', 'published_at', 'synced_at')]
class Video extends Model
{
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

    protected static function booted(): void
    {
        static::creating(function (Video $video) {
            if (empty($video->slug)) {
                $video->slug = Str::slug($video->title);
            }
        });
    }

    public function getYoutubeUrlAttribute(): string
    {
        return "https://www.youtube.com/watch?v={$this->youtube_id}";
    }

    public function getEmbedUrlAttribute(): string
    {
        return "https://www.youtube.com/embed/{$this->youtube_id}";
    }

    public function getFormattedDurationAttribute(): ?string
    {
        if (! $this->duration) {
            return null;
        }

        // Parse ISO 8601 duration (PT1H2M3S)
        try {
            $interval = new \DateInterval($this->duration);
            $parts = [];

            if ($interval->h > 0) {
                $parts[] = $interval->h.':'.str_pad((string) $interval->i, 2, '0', STR_PAD_LEFT);
            } else {
                $parts[] = (string) $interval->i;
            }

            $parts[] = str_pad((string) $interval->s, 2, '0', STR_PAD_LEFT);

            return implode(':', $parts);
        } catch (\Exception) {
            return $this->duration;
        }
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }
}
