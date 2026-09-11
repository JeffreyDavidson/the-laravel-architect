<?php

namespace App\Data;

final readonly class YouTubeVideoData
{
    public function __construct(
        public string $youtubeId,
        public string $title,
        public ?string $description,
        public ?string $thumbnailUrl,
        public ?string $duration,
        public int $viewCount,
        public int $likeCount,
        public int $commentCount,
        public ?string $publishedAt,
    ) {}

    /** @return array{youtube_id: string, title: string, description: ?string, thumbnail_url: ?string, duration: ?string, view_count: int, like_count: int, comment_count: int, published_at: ?string} */
    public function toArray(): array
    {
        return [
            'youtube_id' => $this->youtubeId,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnailUrl,
            'duration' => $this->duration,
            'view_count' => $this->viewCount,
            'like_count' => $this->likeCount,
            'comment_count' => $this->commentCount,
            'published_at' => $this->publishedAt,
        ];
    }
}
