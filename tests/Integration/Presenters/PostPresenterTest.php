<?php

use App\Models\Post;
use App\Presenters\PostPresenter;
use App\Services\ResponsiveImageVariants;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;

use function Pest\Laravel\withVite;

covers(PostPresenter::class);

it('uses the uploaded featured image with its responsive variants', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('post.png', 1400, 700);
    Storage::disk('public')->put('posts/post.png', $image->getContent());
    app(ResponsiveImageVariants::class)->generate('posts/post.png');
    $post = new Post([
        'slug' => 'hello-world-why-im-starting-this-blog',
        'featured_image_path' => 'posts/post.png',
    ]);

    $artwork = PostPresenter::from($post)->artwork();

    expect($artwork?->src)->toBe(Storage::disk('public')->url('posts/post.png'))
        ->and($artwork?->srcset)
        ->toBe(app(ResponsiveImageVariants::class)->srcset('posts/post.png'))
        ->toContain('post-640.webp 640w');
});

it('uses the bundled launch artwork for a post without an upload', function () {
    withVite();
    $small = Vite::asset('resources/images/post-hello-world-384.webp');
    $medium = Vite::asset('resources/images/post-hello-world-768.webp');
    $large = Vite::asset('resources/images/post-hello-world-1280.webp');
    $post = new Post(['slug' => 'hello-world-why-im-starting-this-blog']);

    $artwork = PostPresenter::from($post)->artwork();

    expect($artwork?->src)->toBe($large)
        ->and($artwork?->srcset)
        ->toBe("{$small} 384w, {$medium} 768w, {$large} 1280w");
});

it('has no artwork for a post without an upload or bundled artwork', function () {
    $post = new Post(['slug' => 'a-brand-new-post']);

    expect(PostPresenter::from($post)->artwork())->toBeNull();
});

it('shares the uploaded image, then the bundled artwork, then the generated card', function (?string $path, string $slug, string $expected) {
    withVite();
    Storage::fake('public', ['url' => 'https://example.test/storage']);
    $post = new Post([
        'slug' => $slug,
        'featured_image_path' => $path,
    ]);

    $shareImageUrl = PostPresenter::from($post)->shareImageUrl();

    expect($shareImageUrl)->toContain($expected);
})->with([
    'uploaded image' => ['posts/post.png', 'hello-world-why-im-starting-this-blog', 'https://example.test/storage/posts/post.png'],
    'bundled artwork' => [null, 'hello-world-why-im-starting-this-blog', 'post-hello-world-1280'],
    'generated card' => [null, 'a-brand-new-post', '/og-image/a-brand-new-post'],
]);
