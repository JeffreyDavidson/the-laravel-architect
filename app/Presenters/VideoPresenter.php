<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\Video;

final readonly class VideoPresenter
{
    public function __construct(private Video $video) {}

    public static function from(Video $video): self
    {
        return app()->make(self::class, ['video' => $video]);
    }

    /** The video's watch page on YouTube. */
    public function youtubeUrl(): string
    {
        return "https://www.youtube.com/watch?v={$this->video->youtube_id}";
    }

    public function duration(): ?string
    {
        if (! $this->video->duration) {
            return null;
        }

        try {
            $interval = new \DateInterval($this->video->duration);
            $parts = [
                $interval->h > 0
                    ? $interval->h.':'.str_pad((string) $interval->i, 2, '0', STR_PAD_LEFT)
                    : (string) $interval->i,
                str_pad((string) $interval->s, 2, '0', STR_PAD_LEFT),
            ];

            return implode(':', $parts);
        } catch (\Exception) {
            return $this->video->duration;
        }
    }
}
