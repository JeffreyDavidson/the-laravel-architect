<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

it('backfills responsive variants for existing post images', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('post.png', 1280, 72);
    Storage::disk('public')->put('posts/post.png', $image->getContent());
    $category = Category::factory()->create();
    $author = User::factory()->create();

    Post::withoutEvents(fn () => Post::factory()
        ->for($category)
        ->for($author, 'author')
        ->published()
        ->create([
            'slug' => 'post',
            'featured_image_path' => 'posts/post.png',
        ]));

    $this->artisanCommand('posts:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 1 post.')
        ->assertSuccessful();

    Storage::disk('public')->assertExists([
        'posts/responsive/post-640.webp',
        'posts/responsive/post-1280.webp',
    ]);

    $this->artisanCommand('posts:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 0 posts.')
        ->expectsOutputToContain('Skipped 1 already verified post.')
        ->assertSuccessful();

    $this->artisanCommand('posts:generate-image-variants', ['--force' => true])
        ->expectsOutputToContain('Generated responsive images for 1 post.')
        ->doesntExpectOutputToContain('Skipped')
        ->assertSuccessful();
});

it('refuses to overlap another post image generation run', function () {
    $lock = Cache::lock('framework/command-posts:generate-image-variants', 60);

    expect($lock->get())->toBeTrue();

    try {
        $this->artisanCommand('posts:generate-image-variants')
            ->expectsOutputToContain('The [posts:generate-image-variants] command is already running.')
            ->assertFailed();
    } finally {
        $lock->release();
    }
});
