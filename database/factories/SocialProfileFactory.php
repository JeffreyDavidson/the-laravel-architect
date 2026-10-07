<?php

namespace Database\Factories;

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialProfile>
 */
class SocialProfileFactory extends Factory
{
    /**
     * An enabled profile shown in the footer but not on the contact page.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'platform' => SocialPlatform::Other,
            'label' => null,
            'url' => fake()->url(),
            'is_enabled' => true,
            'show_in_footer' => true,
            'show_on_contact' => false,
            'sort_order' => 0,
        ];
    }
}
