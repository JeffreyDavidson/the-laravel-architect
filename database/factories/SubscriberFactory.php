<?php

namespace Database\Factories;

use App\Enums\SuppressionReason;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<Subscriber>
 */
class SubscriberFactory extends Factory
{
    /**
     * A confirmed, active subscriber.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()
                ->safeEmail(),
            'subscribed_at' => Date::now()->subDay(),
            'verified_at' => Date::now()->subDay(),
            'unsubscribed_at' => null,
        ];
    }

    /** A sign-up that has not confirmed yet. */
    public function pending(): static
    {
        return $this->state(fn (): array => [
            'subscribed_at' => Date::now(),
            'verified_at' => null,
        ]);
    }

    /** A confirmed subscriber who has since unsubscribed. */
    public function unsubscribed(): static
    {
        return $this->state(fn (): array => [
            'unsubscribed_at' => Date::now(),
        ]);
    }

    /** An address that stopped receiving mail after a bounce or complaint. */
    public function suppressed(SuppressionReason $reason = SuppressionReason::Bounced): static
    {
        return $this->state(fn (): array => [
            'unsubscribed_at' => Date::now(),
            'suppressed_at' => Date::now(),
            'suppression_reason' => $reason,
        ]);
    }
}
