<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\ViewModels\PostShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds the post detail payload', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);
    $post = Post::query()->create([
        'title' => 'Current Post',
        'slug' => 'current-post',
        'excerpt' => 'The current post.',
        'content' => 'Current post content.',
        'category_id' => $category->id,
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $relatedPost = Post::query()->create([
        'title' => 'Related Post',
        'slug' => 'related-post',
        'excerpt' => 'A related post.',
        'content' => 'Related post content.',
        'category_id' => $category->id,
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDays(2),
    ]);

    $data = app(PostShowViewModel::class)
        ->data($post);

    expect($data)->toHaveKeys(['post', 'relatedPosts', 'seoSource'])
        ->and($data['post']->is($post))->toBeTrue()
        ->and($data['post']->relationLoaded('category'))->toBeTrue()
        ->and($data['post']->relationLoaded('tags'))->toBeTrue()
        ->and($data['post']->relationLoaded('author'))->toBeTrue()
        ->and($data['relatedPosts']->modelKeys())->toBe([$relatedPost->getKey()])
        ->and($data['seoSource']->is($post))->toBeTrue();
});
