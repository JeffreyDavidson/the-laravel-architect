<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * A draft post with every required-to-publish detail filled in.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'featured_image_path' => null,
            'status' => PublishStatus::Draft,
            'published_at' => null,
        ];
    }

    /** A post that is waiting for editorial review. */
    public function inReview(): static
    {
        return $this->state(fn (): array => [
            'status' => PublishStatus::InReview,
        ]);
    }

    /** A post that went live yesterday. */
    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    /** A post that goes live tomorrow. */
    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => PublishStatus::Scheduled,
            'published_at' => now()->addDay(),
        ]);
    }
}
