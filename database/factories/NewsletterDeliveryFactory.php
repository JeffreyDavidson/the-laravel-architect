<?php

namespace Database\Factories;

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
            'newsletter_issue_id' => NewsletterIssue::factory()->sent(),
            'subscriber_id' => Subscriber::factory(),
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
