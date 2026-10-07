<?php

namespace Database\Factories;

use App\Models\Podcast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Podcast>
 */
class PodcastFactory extends Factory
{
    /**
     * An active podcast without cover artwork.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()
                ->words(3, true),
            'description' => fake()->sentence(),
            'cover_image_path' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /** A podcast hidden from the public site. */
    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
