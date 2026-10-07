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
        $seconds = $this->episode->duration_seconds;

        if (! $seconds) {
            return '';
        }

        $totalMinutes = max(1, (int) round($seconds / 60));
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes} min";
    }

    /** Whether the episode links to YouTube, which gives it a video block on its page. */
    public function hasYouTube(): bool
    {
        return is_string($this->episode->youtube_url)
            && str_contains($this->episode->youtube_url, 'youtu');
    }

    /** The video ID from a youtube.com watch or embed link or a youtu.be link, for the embedded player. */
    public function youtubeVideoId(): ?string
    {
        if (! $this->hasYouTube()) {
            return null;
        }

        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', (string) $this->episode->youtube_url, $matches);

        return $matches[1] ?? null;
    }
}
