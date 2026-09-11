<?php

namespace App\Presenters;

use App\Models\Episode;

final readonly class EpisodePresenter
{
    public function __construct(private Episode $episode) {}

    public static function from(Episode $episode): self
    {
        return new self($episode);
    }

    public function code(): string
    {
        return 'S'.str_pad((string) $this->episode->season_number, 2, '0', STR_PAD_LEFT)
            .'E'.str_pad((string) ($this->episode->episode_number ?? 0), 2, '0', STR_PAD_LEFT);
    }

    public function duration(): string
    {
        if (! $this->episode->duration_minutes) {
            return '';
        }

        $hours = intdiv($this->episode->duration_minutes, 60);
        $minutes = $this->episode->duration_minutes % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes} min";
    }
}
