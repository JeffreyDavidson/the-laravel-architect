<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\NewsletterIssue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterIssue>
 */
class NewsletterIssueFactory extends Factory
{
    /**
     * A draft issue that has not been emailed.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'excerpt' => null,
            'content' => fake()->paragraph(),
            'status' => PublishStatus::Draft,
            'published_at' => null,
            'sent_at' => null,
        ];
    }

    /** An issue that went live on the site yesterday and has not been emailed. */
    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    /** An issue that goes live tomorrow. */
    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => PublishStatus::Scheduled,
            'published_at' => now()->addDay(),
        ]);
    }

    /** A published issue that has been emailed to subscribers. */
    public function sent(): static
    {
        return $this->published()
            ->state(fn (): array => [
                'sent_at' => now(),
            ]);
    }
}
