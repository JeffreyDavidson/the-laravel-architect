<?php

use App\Enums\TestimonialStatus;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

it('records content changes in the activity log', function () {
    $testimonial = Testimonial::query()->create([
        'name' => 'Jane Doe',
        'body' => 'Great work.',
    ]);

    $testimonial->update(['status' => TestimonialStatus::Approved]);

    expect(Activity::query()->forSubject($testimonial)->count())->toBe(2);
});
