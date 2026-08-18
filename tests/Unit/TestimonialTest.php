<?php

use App\Enums\TestimonialStatus;
use App\Models\Testimonial;

it('returns its cast status', function () {
    $testimonial = new Testimonial([
        'status' => TestimonialStatus::Approved,
    ]);

    expect($testimonial->testimonialStatus())->toBe(TestimonialStatus::Approved);
});
