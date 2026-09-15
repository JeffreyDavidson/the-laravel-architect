<?php

namespace Database\Factories;

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Models\ContactInquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactInquiry>
 */
class ContactInquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'type' => fake()->randomElement(ContactType::cases())->value,
            'budget' => null,
            'message' => fake()->sentence(),
            'project_title' => null,
            'status' => ContactInquiryStatus::New,
            'notes' => null,
        ];
    }
}
