<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Podcast extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (Podcast $podcast) {
            if (empty($podcast->slug)) {
                $podcast->slug = Str::slug($podcast->name);
            }
        });
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function publishedEpisodes(): HasMany
    {
        return $this->episodes()
            ->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function latestEpisode()
    {
        return $this->publishedEpisodes()->latest('published_at')->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
