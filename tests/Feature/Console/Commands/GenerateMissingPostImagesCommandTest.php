<?php

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Models\User;
use App\Services\FeaturedImageGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;

pest()->use(RefreshDatabase::class);

it('refreshes responsive variants when generation overwrites the same source path', function () {
    Storage::fake('public');
    $path = 'featured-images/same.png';
    Storage::disk('public')->put($path, UploadedFile::fake()->image('same.png', 1280, 8)
        ->getContent());
    Post::query()->create([
        'title' => 'Same path', 'slug' => 'same-path', 'content' => 'Content',
        'user_id' => User::factory()->create()
            ->id, 'featured_image_path' => $path,
    ]);
    Storage::disk('public')->assertExists('featured-images/responsive/same-1280.webp');
    $generator = Double::for(FeaturedImageGenerator::class);
    $generator->expects('generate')
        ->resolves(function () use ($path): string {
            Storage::disk('public')->put($path, UploadedFile::fake()->image('same.png', 640, 8)
                ->getContent());

            return $path;
        });
    app()->instance(FeaturedImageGenerator::class, $generator);

    $this->artisanCommand('posts:generate-images', ['--force' => true])
        ->assertSuccessful();

    Storage::disk('public')->assertExists('featured-images/responsive/same-640.webp');
    Storage::disk('public')->assertMissing('featured-images/responsive/same-1280.webp');
});

it('succeeds without invoking the generator when no posts need images', function () {
    $generator = Double::for(FeaturedImageGenerator::class);
    $generator->allows('generate')
        ->never();
    app()->instance(FeaturedImageGenerator::class, $generator);

    $this->artisanCommand('posts:generate-images')
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
        ->with(Argument::satisfies(fn (mixed $post): bool => $post instanceof Post && $post->is($missingImage)))
        ->returns('featured-images/generated.png');
    app()->instance(FeaturedImageGenerator::class, $generator);

    $this->artisanCommand('posts:generate-images')
        ->expectsOutput('Generated: featured-images/generated.png')
        ->expectsOutput('Done! Generated images for 1 posts.')
        ->assertSuccessful();

    expect($missingImage->refresh()
        ->featured_image_path)->toBe('featured-images/generated.png')
        ->and($existingImage->refresh()
            ->featured_image_path)
        ->toBe('featured-images/existing.png');
});

it('regenerates and persists generated and missing post images when forced', function () {
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

    // One expectation returns each post's image in processing order; the
    // assertions below prove each post saved its own path.
    $generator = Double::for(FeaturedImageGenerator::class);
    $generator->expects('generate')
        ->with(Argument::satisfies(fn (mixed $post): bool => $post instanceof Post))
        ->times(2)
        ->returns('featured-images/new-first.png', 'featured-images/new-second.png');
    app()->instance(FeaturedImageGenerator::class, $generator);

    $this->artisanCommand('posts:generate-images', ['--force' => true])
        ->expectsOutput('Generated: featured-images/new-first.png')
        ->expectsOutput('Generated: featured-images/new-second.png')
        ->expectsOutput('Done! Generated images for 2 posts.')
        ->assertSuccessful();

    expect($firstPost->refresh()
        ->featured_image_path)->toBe('featured-images/new-first.png')
        ->and($secondPost->refresh()
            ->featured_image_path)
        ->toBe('featured-images/new-second.png');
});

it('keeps uploaded post images when forced', function () {
    Storage::fake('public');
    $uploadPath = 'posts/upload.webp';
    Storage::disk('public')->put($uploadPath, UploadedFile::fake()->image('upload.webp', 640, 8)
        ->getContent());
    $user = User::factory()->create();
    $uploadedImage = Post::query()->create([
        'title' => 'Uploaded Image',
        'slug' => 'uploaded-image',
        'content' => 'Content',
        'user_id' => $user->id,
        'featured_image_path' => $uploadPath,
    ]);
    $missingImage = Post::query()->create([
        'title' => 'Missing Image',
        'slug' => 'missing-image',
        'content' => 'Content',
        'user_id' => $user->id,
    ]);

    $generator = Double::for(FeaturedImageGenerator::class);
    $generator->expects('generate')
        ->with(Argument::satisfies(fn (mixed $post): bool => $post instanceof Post && $post->is($missingImage)))
        ->returns('featured-images/missing-image.png');
    app()->instance(FeaturedImageGenerator::class, $generator);

    $this->artisanCommand('posts:generate-images', ['--force' => true])
        ->expectsOutput('Done! Generated images for 1 posts.')
        ->assertSuccessful();

    expect($uploadedImage->refresh()
        ->featured_image_path)->toBe($uploadPath);
    Storage::disk('public')->assertExists($uploadPath);
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
        ->with(Argument::satisfies(fn (mixed $generatedPost): bool => $generatedPost instanceof Post && $generatedPost->is($post)))
        ->throws(new RuntimeException('Image generation failed.'));
    app()->instance(FeaturedImageGenerator::class, $generator);

    expect(fn () => Artisan::call('posts:generate-images'))
        ->toThrow(RuntimeException::class, 'Image generation failed.')
        ->and($post->refresh()
            ->featured_image_path)
        ->toBeNull();
});
