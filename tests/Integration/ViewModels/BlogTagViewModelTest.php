<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\ViewModels\BlogTagViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RalphJSmit\Laravel\SEO\Support\SEOData;

uses(RefreshDatabase::class);

it('builds a paginated tag archive payload', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);
    $tag = Tag::query()->create([
        'name' => 'Boundaries',
        'slug' => 'boundaries',
    ]);

    foreach (range(1, 11) as $index) {
        $post = Post::query()->create([
            'title' => "Boundary Article {$index}",
            'slug' => "boundary-article-{$index}",
            'content' => 'A maintainable application starts with clear boundaries.',
            'category_id' => $category->getKey(),
            'user_id' => $author->getKey(),
            'status' => PublishStatus::Published,
            'published_at' => now()->subDays($index),
        ]);

        $post->attachTag($tag);
    }

    $draft = Post::query()->create([
        'title' => 'Boundary Draft',
        'slug' => 'boundary-draft',
        'content' => 'This draft must remain private.',
        'category_id' => $category->getKey(),
        'user_id' => $author->getKey(),
        'status' => PublishStatus::Draft,
    ]);
    $draft->attachTag($tag);

    request()->query->set('page', 2);

    $data = app(BlogTagViewModel::class)
        ->data($tag);

    $canonicalUrl = route('blog.tag', ['tag' => $tag, 'page' => 2]);

    expect($data)->toHaveKeys(['tag', 'posts', 'seoSource'])
        ->and($data['tag']->is($tag))->toBeTrue()
        ->and($data['posts']->currentPage())->toBe(2)
        ->and($data['posts']->total())->toBe(11)
        ->and($data['posts']->sole()->relationLoaded('category'))->toBeTrue()
        ->and($data['posts']->sole()->relationLoaded('author'))->toBeTrue()
        ->and($data['seoSource'])->toBeInstanceOf(SEOData::class)
        ->and($data['seoSource']->title)->toBe('Boundaries Articles — Page 2')
        ->and($data['seoSource']->description)->toBe(
            'Articles tagged with Boundaries on The Laravel Architect. Page 2 of 2.',
        )
        ->and($data['seoSource']->url)->toBe($canonicalUrl)
        ->and($data['seoSource']->canonical_url)->toBe($canonicalUrl);
});
