<?php

namespace App\Presenters;

use App\Models\Video;

final readonly class VideoPresenter
{
    public function __construct(private Video $video) {}

    public static function from(Video $video): self
    {
        return new self($video);
    }

    public function duration(): ?string
    {
        if (! $this->video->duration) {
            return null;
        }

        try {
            $interval = new \DateInterval($this->video->duration);
            $parts = [];

            if ($interval->h > 0) {
                $parts[] = $interval->h.':'.str_pad((string) $interval->i, 2, '0', STR_PAD_LEFT);
            } else {
                $parts[] = (string) $interval->i;
            }

            $parts[] = str_pad((string) $interval->s, 2, '0', STR_PAD_LEFT);

            return implode(':', $parts);
        } catch (\Exception) {
            return $this->video->duration;
        }
    }
}
