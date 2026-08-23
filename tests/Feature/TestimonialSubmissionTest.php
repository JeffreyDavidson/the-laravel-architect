<?php

use App\Enums\TestimonialStatus;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders an accessible testimonial submission page', function () {
    $this->get(route('testimonials.create'))
        ->assertOk()
        ->assertSee('Share your', false)
        ->assertSee('for="testimonial-name"', false)
        ->assertSee('for="testimonial-role"', false)
        ->assertSee('for="testimonial-company"', false)
        ->assertSee('for="testimonial-body"', false)
        ->assertSee('aria-describedby="testimonial-body-help"', false);
});

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

it('validates testimonial submissions', function () {
    $this->from(route('testimonials.create'))
        ->post(route('testimonials.store'), [
            'name' => '',
            'role' => 'CTO',
            'body' => '',
        ])
        ->assertRedirect(route('testimonials.create'))
        ->assertSessionHasErrors(['name', 'body'])
        ->assertSessionHasInput('role', 'CTO');

    expect(Testimonial::query()->count())->toBe(0);
});
