<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Episode extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Episode $episode) {
            if (empty($episode->slug)) {
                $episode->slug = Str::slug($episode->title);
            }
        });
    }

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_minutes) return '';
        $hours = intdiv($this->duration_minutes, 60);
        $mins = $this->duration_minutes % 60;
        return $hours > 0 ? "{$hours}h {$mins}m" : "{$mins} min";
    }

    public function getEpisodeCodeAttribute(): string
    {
        return 'S' . str_pad($this->season_number, 2, '0', STR_PAD_LEFT) 
            . 'E' . str_pad($this->episode_number, 2, '0', STR_PAD_LEFT);
    }
}
