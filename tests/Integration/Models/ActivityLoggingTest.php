<?php

use App\Enums\TestimonialStatus;
use App\Models\Post;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

it('records operational post changes without recording long-form content', function () {
    $post = Post::query()->create([
        'title' => 'Activity logging',
        'content' => 'Original content.',
        'user_id' => User::factory()->create()->id,
    ]);
    $post->refresh();

    $initialActivityCount = Activity::query()->forSubject($post)->count();

    $post->update(['content' => 'Updated content.']);

    expect(Activity::query()->forSubject($post)->count())->toBe($initialActivityCount);

    $post->update(['title' => 'Updated activity logging']);

    $activity = Activity::query()->forSubject($post)->latest('id')->firstOrFail();
    $attributes = $activity->attribute_changes->get('attributes');

    expect($attributes)
        ->toHaveKey('title', 'Updated activity logging')
        ->not->toHaveKeys(['content', 'excerpt', 'review_notes']);
});

it('does not include testimonial personal details in the activity log', function () {
    $testimonial = Testimonial::query()->create([
        'name' => 'Private customer',
        'role' => 'Developer',
        'company' => 'Private Company',
        'body' => 'Private testimonial copy.',
    ]);
    $testimonial->refresh();

    $initialActivityCount = Activity::query()->forSubject($testimonial)->count();

    $testimonial->update([
        'name' => 'Updated private customer',
        'body' => 'Updated private testimonial copy.',
    ]);

    expect(Activity::query()->forSubject($testimonial)->count())->toBe($initialActivityCount);

    $testimonial->update(['status' => TestimonialStatus::Approved]);

    $activity = Activity::query()->forSubject($testimonial)->latest('id')->firstOrFail();
    $attributes = $activity->attribute_changes->get('attributes');

    expect($attributes)
        ->toHaveKey('status', TestimonialStatus::Approved->value)
        ->not->toHaveKeys(['name', 'role', 'company', 'body']);
});
