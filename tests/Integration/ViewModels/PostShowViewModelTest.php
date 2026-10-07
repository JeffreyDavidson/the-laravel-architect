<?php

use App\Models\Category;
use App\Models\Post;
use App\ViewModels\PostShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('builds the post detail payload', function () {
    $category = Category::factory()->create();
    $post = Post::factory()
        ->for($category)
        ->published()
        ->create(['title' => 'Current Post']);
    $relatedPost = Post::factory()
        ->for($category)
        ->published()
        ->create();

    $data = app(PostShowViewModel::class)
        ->data($post);

    expect($data)->toHaveKeys(['post', 'relatedPosts', 'seoSource'])
        ->and($data['post']->is($post))
        ->toBeTrue()
        ->and($data['post']->relationLoaded('category'))
        ->toBeTrue()
        ->and($data['post']->relationLoaded('tags'))
        ->toBeTrue()
        ->and($data['post']->relationLoaded('author'))
        ->toBeTrue()
        ->and($data['relatedPosts']->modelKeys())
        ->toBe([$relatedPost->getKey()])
        ->and($data['seoSource']->title)
        ->toBe('Current Post')
        ->and($data['seoSource']->type)
        ->toBe('article');
});
