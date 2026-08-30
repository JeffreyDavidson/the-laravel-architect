<?php

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('persists every supported post publish status', function () {
    $author = User::factory()->create();

    foreach (PublishStatus::cases() as $status) {
        $post = Post::query()->create([
            'title' => "{$status->label()} post",
            'slug' => "{$status->value}-post",
            'content' => 'Post content.',
            'user_id' => $author->id,
            'status' => $status,
        ]);

        expect($post->refresh()->status)->toBe($status);
    }
});

it('rejects post publish statuses that the application does not support', function () {
    $author = User::factory()->create();

    DB::table('posts')->insert([
        'title' => 'Unknown status post',
        'slug' => 'unknown-status-post',
        'content' => 'Post content.',
        'user_id' => $author->id,
        'status' => 'unknown',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
})->throws(QueryException::class);
