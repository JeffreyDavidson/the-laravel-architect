<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Episode>
 */
class EpisodeFactory extends Factory
{
    /**
     * A draft episode with every required-to-publish detail filled in.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'podcast_id' => Podcast::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'transistor_url' => 'https://share.transistor.fm/s/428dcd6b',
            'featured_image_path' => null,
            'status' => PublishStatus::Draft,
            'published_at' => null,
        ];
    }

    /** An episode that went live yesterday. */
    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    /** An episode that goes live tomorrow. */
    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => PublishStatus::Scheduled,
            'published_at' => now()->addDay(),
        ]);
    }
}
