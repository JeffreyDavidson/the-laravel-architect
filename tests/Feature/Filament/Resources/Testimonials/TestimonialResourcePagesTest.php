<?php

use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders the testimonial edit page for an authorized user', function () {
    $testimonial = Testimonial::query()->create([
        'name' => 'Testimonial page coverage',
        'role' => 'Developer',
        'company' => 'Laravel Community',
        'body' => 'This testimonial verifies the edit page renders.',
        'status' => 'approved',
        'sort_order' => 1,
    ]);

    $this->get(TestimonialResource::getUrl('edit', ['record' => $testimonial]))
        ->assertOk();
});
