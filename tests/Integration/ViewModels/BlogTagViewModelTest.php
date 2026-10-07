<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\ViewModels\BlogTagViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('builds a paginated tag archive payload', function () {
    $author = User::factory()->create();
    $category = Category::factory()->create();
    $tag = Tag::factory()->create(['name' => 'Boundaries']);

    foreach (range(1, 11) as $index) {
        $post = Post::factory()
            ->for($category)
            ->for($author, 'author')
            ->published()
            ->create(['published_at' => now()->subDays($index)]);

        $post->attachTag($tag);
    }

    $draft = Post::factory()
        ->for($category)
        ->for($author, 'author')
        ->create();
    $draft->attachTag($tag);

    request()->query->set('page', 2);

    $data = app(BlogTagViewModel::class)
        ->data($tag);

    $canonicalUrl = route('blog.tag', ['tag' => $tag, 'page' => 2]);

    expect($data)->toHaveKeys(['tag', 'posts', 'seoSource'])
        ->and($data['tag']->is($tag))
        ->toBeTrue()
        ->and($data['posts']->currentPage())
        ->toBe(2)
        ->and($data['posts']->total())
        ->toBe(11)
        ->and($data['posts']->sole()
            ->relationLoaded('category'))
        ->toBeTrue()
        ->and($data['posts']->sole()
            ->relationLoaded('author'))
        ->toBeTrue()
        ->and($data['seoSource']->title)
        ->toBe('Articles Tagged Boundaries — Page 2')
        ->and($data['seoSource']->description)
        ->toBe(
            'Articles tagged with Boundaries on The Laravel Architect. Page 2 of 2.',
        )
        ->and($data['seoSource']->url)
        ->toBe($canonicalUrl)
        ->and($data['seoSource']->canonical_url)
        ->toBe($canonicalUrl);
});
