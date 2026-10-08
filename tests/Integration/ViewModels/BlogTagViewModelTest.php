<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\ViewModels\BlogTagViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StructuredDataExpectations as Schema;

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

    expect($data)->toHaveKeys(['tag', 'posts', 'pageMeta'])
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
        ->and($data['pageMeta']->seo->title)
        ->toBe('Articles Tagged Boundaries — Page 2')
        ->and($data['pageMeta']->seo->description)
        ->toBe(
            'Articles tagged with Boundaries on The Laravel Architect. Page 2 of 2.',
        )
        ->and($data['pageMeta']->seo->url)
        ->toBe($canonicalUrl)
        ->and($data['pageMeta']->seo->canonical_url)
        ->toBe($canonicalUrl);
});

it('offsets a paginated tag listing', function () {
    Schema::useFixedOrigin();
    $tag = Tag::factory()->create(['name' => 'livewire']);

    foreach (range(1, 11) as $day) {
        Post::factory()
            ->published()
            ->create(['title' => "Post {$day}", 'slug' => "post-{$day}", 'published_at' => now()->subDays($day)])
            ->attachTag($tag);
    }

    request()->query->set('page', 2);

    $data = app(BlogTagViewModel::class)
        ->data($tag);

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        ...Schema::collection('Articles Tagged livewire', 'https://example.test/blog/tag/livewire?page=2', [
            11 => ['Post 11', 'https://example.test/blog/post-11'],
        ]),
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Blog', 'https://example.test/blog'],
            ['livewire', 'https://example.test/blog/tag/livewire?page=2'],
        ]),
    ]);
});
