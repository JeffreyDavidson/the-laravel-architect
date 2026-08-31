<?php

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('backfills responsive variants for existing post images', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('post.png', 1280, 72);
    Storage::disk('public')->put('posts/post.png', $image->getContent());
    $author = User::factory()->create();

    Post::withoutEvents(fn () => Post::query()->create([
        'title' => 'Post',
        'slug' => 'post',
        'content' => 'Content',
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => now(),
        'featured_image_path' => 'posts/post.png',
    ]));

    $this->artisan('posts:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 1 post.')
        ->assertSuccessful();

    Storage::disk('public')->assertExists([
        'posts/responsive/post-640.webp',
        'posts/responsive/post-1280.webp',
    ]);

    $this->artisan('posts:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 0 posts.')
        ->expectsOutputToContain('Skipped 1 already verified post.')
        ->assertSuccessful();

    $this->artisan('posts:generate-image-variants', ['--force' => true])
        ->expectsOutputToContain('Generated responsive images for 1 post.')
        ->doesntExpectOutputToContain('Skipped')
        ->assertSuccessful();
});
