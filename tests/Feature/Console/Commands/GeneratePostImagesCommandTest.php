<?php

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Models\User;
use App\Services\FeaturedImageGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;

uses(RefreshDatabase::class);

it('succeeds without invoking the generator when no posts need images', function () {
    $generator = Double::for(FeaturedImageGenerator::class);
    $generator->allows('generate')->never();
    app()->instance(FeaturedImageGenerator::class, $generator);

    $this->artisan('posts:generate-images')
        ->expectsOutput('No posts need images generated.')
        ->assertSuccessful();
});

it('generates and persists images only for posts without one by default', function () {
    $user = User::factory()->create();
    $missingImage = Post::query()->create([
        'title' => 'Missing Image',
        'slug' => 'missing-image',
        'content' => 'Content',
        'user_id' => $user->id,
        'status' => PublishStatus::Draft,
    ]);
    $existingImage = Post::query()->create([
        'title' => 'Existing Image',
        'slug' => 'existing-image',
        'content' => 'Content',
        'user_id' => $user->id,
        'status' => PublishStatus::Draft,
        'featured_image_path' => 'featured-images/existing.png',
    ]);

    $generator = Double::for(FeaturedImageGenerator::class);
    $generator->expects('generate')
        ->with(Argument::satisfies(fn (Post $post): bool => $post->is($missingImage)))
        ->returns('featured-images/generated.png');
    app()->instance(FeaturedImageGenerator::class, $generator);

    $this->artisan('posts:generate-images')
        ->expectsOutput('Generated: featured-images/generated.png')
        ->expectsOutput('Done! Generated images for 1 posts.')
        ->assertSuccessful();

    expect($missingImage->refresh()->featured_image_path)->toBe('featured-images/generated.png')
        ->and($existingImage->refresh()->featured_image_path)->toBe('featured-images/existing.png');
});

it('regenerates and persists every post image when forced', function () {
    $user = User::factory()->create();
    $firstPost = Post::query()->create([
        'title' => 'First Post',
        'slug' => 'first-post',
        'content' => 'Content',
        'user_id' => $user->id,
        'status' => PublishStatus::Draft,
        'featured_image_path' => 'featured-images/old-first.png',
    ]);
    $secondPost = Post::query()->create([
        'title' => 'Second Post',
        'slug' => 'second-post',
        'content' => 'Content',
        'user_id' => $user->id,
        'status' => PublishStatus::Draft,
    ]);

    $generator = Double::for(FeaturedImageGenerator::class);
    $generator->expects('generate')
        ->with(Argument::satisfies(fn (Post $post): bool => $post->is($firstPost)))
        ->returns('featured-images/new-first.png')
        ->ordered();
    $generator->expects('generate')
        ->with(Argument::satisfies(fn (Post $post): bool => $post->is($secondPost)))
        ->returns('featured-images/new-second.png')
        ->ordered();
    app()->instance(FeaturedImageGenerator::class, $generator);

    $this->artisan('posts:generate-images', ['--force' => true])
        ->expectsOutput('Generated: featured-images/new-first.png')
        ->expectsOutput('Generated: featured-images/new-second.png')
        ->expectsOutput('Done! Generated images for 2 posts.')
        ->assertSuccessful();

    expect($firstPost->refresh()->featured_image_path)->toBe('featured-images/new-first.png')
        ->and($secondPost->refresh()->featured_image_path)->toBe('featured-images/new-second.png');
});

it('propagates generator failures without persisting an image path', function () {
    $user = User::factory()->create();
    $post = Post::query()->create([
        'title' => 'Failed Image',
        'slug' => 'failed-image',
        'content' => 'Content',
        'user_id' => $user->id,
        'status' => PublishStatus::Draft,
    ]);

    $generator = Double::for(FeaturedImageGenerator::class);
    $generator->expects('generate')
        ->with(Argument::satisfies(fn (Post $generatedPost): bool => $generatedPost->is($post)))
        ->throws(new RuntimeException('Image generation failed.'));
    app()->instance(FeaturedImageGenerator::class, $generator);

    expect(fn () => Artisan::call('posts:generate-images'))
        ->toThrow(RuntimeException::class, 'Image generation failed.');

    expect($post->refresh()->featured_image_path)->toBeNull();
});
