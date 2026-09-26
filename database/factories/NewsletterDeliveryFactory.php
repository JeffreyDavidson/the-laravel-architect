<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterDelivery>
 */
class NewsletterDeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'newsletter_issue_id' => fn (): mixed => NewsletterIssue::query()
                ->create([
                    'title' => fake()->sentence(3),
                    'content' => fake()->paragraph(),
                    'status' => PublishStatus::Published,
                    'published_at' => now()->subDay(),
                    'sent_at' => now(),
                ])
                ->getKey(),
            'subscriber_id' => fn (): mixed => Subscriber::query()
                ->create([
                    'email' => fake()
                        ->unique()
                        ->safeEmail(),
                    'subscribed_at' => now()->subMonth(),
                    'verified_at' => now()->subMonth(),
                ])
                ->getKey(),
            'sent_at' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'sent_at' => now(),
        ]);
    }
}
