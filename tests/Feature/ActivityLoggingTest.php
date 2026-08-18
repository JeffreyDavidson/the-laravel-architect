<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

it('records content changes in the activity log', function () {
    $post = Post::query()->create([
        'title' => 'Activity logging',
        'content' => 'Original content.',
        'user_id' => User::factory()->create()->id,
    ]);

    $post->update(['content' => 'Updated content.']);

    expect(Activity::query()->forSubject($post)->count())->toBe(2);
});
