<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Presenters\PostPresenter;
use App\ViewModels\PostIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Support\StructuredDataExpectations as Schema;

pest()->use(RefreshDatabase::class);

it('builds the public blog index payload', function () {
    $author = User::factory()->create();
    $category = Category::factory()->create();
    $olderPost = createPostIndexViewModelPost(
        author: $author,
        category: $category,
        title: 'Older Post',
        publishedAt: now()->subDays(2),
    );
    $newerPost = createPostIndexViewModelPost(
        author: $author,
        category: $category,
        title: 'Newer Post',
        publishedAt: now()->subDay(),
    );
    createPostIndexViewModelPost(
        author: $author,
        category: $category,
        title: 'Draft Post',
        publishedAt: null,
        status: PublishStatus::Draft,
    );

    $data = blogIndexViewModelData();

    expect($data)->toHaveKeys(['posts', 'categories', 'pageMeta'])
        ->and($data['posts']->getCollection()
            ->modelKeys())
        ->toBe([
            $newerPost->getKey(),
            $olderPost->getKey(),
        ])
        ->and($data['posts']->every(
            fn (Post $post): bool => $post->relationLoaded('category')
                && $post->relationLoaded('tags'),
        ))->toBeTrue()
        ->and($data['categories']->sole()
            ->posts_count)
        ->toBe(2)
        ->and($data['posts']->total())
        ->toBe(2)
        ->and($data['pageMeta']->seo->title)
        ->toBe('Blog');
});

it('filters the paginated archive by title excerpt and translated tag name', function () {
    $author = User::factory()->create();
    $category = Category::factory()->create();
    $tag = Tag::factory()->create(['name' => 'Boundaries']);

    foreach ([
        ['title' => 'Title Match', 'excerpt' => 'Nothing special.'],
        ['title' => 'Unrelated', 'excerpt' => 'Excerpt Match.'],
        ['title' => 'Another Article', 'excerpt' => 'Nothing special.', 'tag' => true],
    ] as $index => $attributes) {
        $post = Post::factory()
            ->for($category)
            ->for($author, 'author')
            ->published()
            ->create([
                'title' => $attributes['title'],
                'excerpt' => $attributes['excerpt'],
                'content' => str_repeat('word ', 251),
                'published_at' => now()->subDays($index + 1),
            ]);

        if ($attributes['tag'] ?? false) {
            $post->attachTag($tag);
        }
    }

    $data = blogIndexViewModelData(['q' => 'boundaries']);

    expect($data['posts']->total())->toBe(1)
        ->and($data['posts']->sole()
            ->title)
        ->toBe('Another Article')
        ->and($data['posts']->sole()
            ->content)
        ->toContain('word')
        ->and(PostPresenter::from($data['posts']->sole())->readingTime())
        ->toBe(2)
        ->and($data['pageMeta']->seo->robots)
        ->toBe('noindex, follow')
        ->and($data['pageMeta']->seo->canonical_url)
        ->toBe(route('blog.index'));

    foreach ([
        'title match' => 'Title Match',
        'excerpt match' => 'Unrelated',
    ] as $term => $expectedTitle) {
        $filtered = blogIndexViewModelData(['q' => $term]);

        expect($filtered['posts']->sole()
            ->title)->toBe($expectedTitle);
    }

    $literal = Post::factory()
        ->for($category)
        ->for($author, 'author')
        ->published()
        ->create([
            'title' => 'Literal %_ Marker',
            'published_at' => now(),
        ]);

    expect(blogIndexViewModelData(['q' => '%'])['posts']->sole()
        ->is($literal))->toBeTrue()
        ->and(blogIndexViewModelData(['q' => '_'])['posts']->sole()
            ->is($literal))
        ->toBeTrue();
});

it('lists the first blog page from position one under the canonical url', function () {
    Schema::useFixedOrigin();
    $author = User::factory()->create();
    $category = Category::factory()->create();
    createPostIndexViewModelPost($author, $category, 'First post', now()->subDay(), slug: 'first-post');
    createPostIndexViewModelPost($author, $category, 'Second post', now()->subDays(2), slug: 'second-post');

    $data = blogIndexViewModelData();

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        ...Schema::collection('Blog', 'https://example.test/blog', [
            1 => ['First post', 'https://example.test/blog/first-post'],
            2 => ['Second post', 'https://example.test/blog/second-post'],
        ]),
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Blog', 'https://example.test/blog'],
        ]),
    ]);
});

it('offsets later blog pages and names a selected category under its archive url', function () {
    Schema::useFixedOrigin();
    $author = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Testing', 'slug' => 'testing']);

    foreach (range(1, 13) as $day) {
        createPostIndexViewModelPost($author, $category, "Post {$day}", now()->subDays($day), slug: "post-{$day}");
    }

    request()->query->set('page', 2);

    $data = blogIndexViewModelData(['category' => 'testing']);

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        ...Schema::collection('Testing Articles', 'https://example.test/blog/category/testing', [
            13 => ['Post 13', 'https://example.test/blog/post-13'],
        ]),
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Blog', 'https://example.test/blog/category/testing'],
        ]),
    ]);
});

it('keeps the listing name under the blog url on a search', function () {
    Schema::useFixedOrigin();
    $author = User::factory()->create();
    $category = Category::factory()->create();
    createPostIndexViewModelPost($author, $category, 'Searched post', now()->subDay(), slug: 'searched-post');

    $data = blogIndexViewModelData(['q' => 'searched']);

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        ...Schema::collection('Blog', 'https://example.test/blog', [
            1 => ['Searched post', 'https://example.test/blog/searched-post'],
        ]),
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Blog', 'https://example.test/blog'],
        ]),
    ]);
});

it('rejects an unknown category', function () {
    expect(fn (): array => blogIndexViewModelData(['category' => 'missing-category']))
        ->toThrow(NotFoundHttpException::class);
});

/**
 * @param  array{q?: string, category?: string}  $filters
 * @return array{
 *     posts: LengthAwarePaginator<int, Post>,
 *     categories: Collection<int, Category>,
 *     publishedPostCount: int,
 *     query: string,
 *     categorySlug: string|null,
 *     selectedCategory: Category|null,
 *     pageMeta: PageMeta,
 * }
 */
function blogIndexViewModelData(array $filters = []): array
{
    $query = trim($filters['q'] ?? '');
    $categorySlug = $filters['category'] ?? null;

    return app(PostIndexViewModel::class)->data($query, $categorySlug);
}

function createPostIndexViewModelPost(
    User $author,
    Category $category,
    string $title,
    ?DateTimeInterface $publishedAt,
    PublishStatus $status = PublishStatus::Published,
    ?string $slug = null,
): Post {
    return Post::factory()
        ->for($category)
        ->for($author, 'author')
        ->create([
            'title' => $title,
            'status' => $status,
            'published_at' => $publishedAt,
            ...$slug === null ? [] : ['slug' => $slug],
        ]);
}
