<?php

use App\Enums\TestimonialStatus;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('loads only the testimonials displayed on the homepage while retaining the approved total', function () {
    foreach (range(1, 5) as $sortOrder) {
        Testimonial::query()->create([
            'name' => "Approved Client {$sortOrder}",
            'body' => "Approved recommendation {$sortOrder}.",
            'status' => TestimonialStatus::Approved,
            'sort_order' => $sortOrder,
        ]);
    }

    Testimonial::query()->create([
        'name' => 'Pending Client',
        'body' => 'Pending recommendation.',
        'status' => TestimonialStatus::Pending,
        'sort_order' => 6,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertViewHas('testimonials', fn ($testimonials): bool => $testimonials->count() === 3)
        ->assertViewHas('approvedTestimonialCount', 5)
        ->assertSee('Approved Client 1')
        ->assertSee('Approved Client 3')
        ->assertDontSee('Approved Client 4')
        ->assertDontSee('Pending Client');
});
