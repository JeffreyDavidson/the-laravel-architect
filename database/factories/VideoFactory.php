<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    /**
     * An unfeatured video that was published on YouTube yesterday.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'youtube_id' => fake()->unique()
                ->regexify('[A-Za-z0-9_-]{11}'),
            'title' => fake()->sentence(4),
            'description' => null,
            'thumbnail_url' => null,
            'is_featured' => false,
            'published_at' => now()->subDay(),
        ];
    }
}
