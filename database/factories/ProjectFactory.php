<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * A draft, unfeatured project with every required-to-publish detail filled in.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'featured_image_path' => null,
            'tech_stack' => null,
            'is_featured' => false,
            'sort_order' => 0,
            'status' => PublishStatus::Draft,
        ];
    }

    /** A project shown on the public site. Projects have no publish date. */
    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PublishStatus::Published,
        ]);
    }

    /** A project highlighted on the home page. */
    public function featured(): static
    {
        return $this->state(fn (): array => [
            'is_featured' => true,
        ]);
    }
}
