<?php

use App\Models\Category;
use App\Models\Post;
use App\ViewModels\PostShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\Support\StructuredDataExpectations as Schema;

use function Pest\Laravel\travelTo;

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

    expect($data)->toHaveKeys(['post', 'relatedPosts', 'pageMeta'])
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
        ->and($data['pageMeta']->seo->title)
        ->toBe('Current Post')
        ->and($data['pageMeta']->seo->type)
        ->toBe('article');
});

it('shares the wide image and fills empty SEO fields from the ones saved in the admin', function (bool $hasSavedRow) {
    Schema::useFixedOrigin();
    $post = Post::factory()
        ->published()
        ->create(['title' => 'Shipping Laravel', 'slug' => 'shipping-laravel', 'excerpt' => null]);

    if ($hasSavedRow) {
        $post->seo()
            ->update([
                'title' => 'Saved title',
                'description' => 'Saved description.',
                'image' => 'posts/saved.png',
                'robots' => 'noindex, follow',
                'canonical_url' => 'https://canonical.test/shipping-laravel',
            ]);
    } else {
        $post->seo()
            ->delete();
    }

    $data = app(PostShowViewModel::class)
        ->data($post->refresh());
    $seo = $data['pageMeta']->seo;

    expect($seo->title)->toBe('Shipping Laravel')
        ->and($seo->description)
        ->toBe($hasSavedRow ? 'Saved description.' : null)
        ->and($seo->image)
        ->toBe('https://example.test/og-image/shipping-laravel')
        ->and($seo->robots)
        ->toBe($hasSavedRow ? 'noindex, follow' : null)
        ->and($seo->canonical_url)
        ->toBe($hasSavedRow ? 'https://canonical.test/shipping-laravel' : null)
        ->and($seo->published_time?->toIso8601String())
        ->toBe($post->published_at?->toIso8601String());
})->with([
    'saved SEO row' => true,
    'no SEO row' => false,
]);

it('describes a post as an article with blog breadcrumbs', function () {
    Schema::useFixedOrigin();
    travelTo(Date::parse('2026-09-02 10:00:00'));
    $post = Post::factory()
        ->published()
        ->create([
            'title' => 'Shipping Laravel',
            'slug' => 'shipping-laravel',
            'excerpt' => 'How it ships.',
            'published_at' => Date::parse('2026-09-01 08:30:00'),
        ]);

    $data = app(PostShowViewModel::class)
        ->data($post);

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        [
            '@type' => 'Article',
            '@id' => 'https://example.test/blog/shipping-laravel#article',
            'url' => 'https://example.test/blog/shipping-laravel',
            'headline' => 'Shipping Laravel',
            'datePublished' => '2026-09-01T08:30:00+00:00',
            'dateModified' => '2026-09-02T10:00:00+00:00',
            'author' => Schema::author(),
            'mainEntityOfPage' => 'https://example.test/blog/shipping-laravel',
            'description' => 'How it ships.',
            'image' => 'https://example.test/og-image/shipping-laravel',
        ],
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Blog', 'https://example.test/blog'],
            ['Shipping Laravel', 'https://example.test/blog/shipping-laravel'],
        ]),
    ]);
});
