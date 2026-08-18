<?php

use App\Enums\TestimonialStatus;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores a valid testimonial as pending', function () {
    $this->post(route('testimonials.store'), [
        'name' => 'Jane Doe',
        'role' => 'CTO',
        'company' => 'Acme',
        'body' => 'Jeffrey made a difficult modernization project straightforward.',
    ])->assertSessionHas('testimonial_success');

    expect(Testimonial::query()->sole()->status)->toBe(TestimonialStatus::Pending);
});

it('silently discards testimonial honeypot submissions', function () {
    $this->post(route('testimonials.store'), [
        'name' => 'Spam Bot',
        'body' => 'Spam',
        'website' => 'https://spam.example',
    ])->assertSessionHas('testimonial_success');

    expect(Testimonial::query()->count())->toBe(0);
});
