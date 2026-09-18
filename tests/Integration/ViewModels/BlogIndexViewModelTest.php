<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Presenters\PostPresenter;
use App\Queries\BlogIndexQuery;
use App\ViewModels\BlogIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('builds the public blog index payload', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);
    $olderPost = createBlogIndexViewModelPost(
        author: $author,
        category: $category,
        title: 'Older Post',
        publishedAt: now()->subDays(2),
    );
    $newerPost = createBlogIndexViewModelPost(
        author: $author,
        category: $category,
        title: 'Newer Post',
        publishedAt: now()->subDay(),
    );
    createBlogIndexViewModelPost(
        author: $author,
        category: $category,
        title: 'Draft Post',
        publishedAt: null,
        status: PublishStatus::Draft,
    );

    $data = blogIndexViewModelData();

    expect($data)->toHaveKeys(['posts', 'categories', 'seoSource'])
        ->and($data['posts']->getCollection()->modelKeys())->toBe([
            $newerPost->getKey(),
            $olderPost->getKey(),
        ])
        ->and($data['posts']->every(
            fn (Post $post): bool => $post->relationLoaded('category')
                && $post->relationLoaded('tags'),
        ))->toBeTrue()
        ->and($data['categories']->sole()->posts_count)->toBe(2)
        ->and($data['posts']->total())->toBe(2)
        ->and($data['seoSource']->title)->toBe('Blog');
});

it('filters the paginated archive by title excerpt and translated tag name', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);
    $tag = Tag::query()->create([
        'name' => ['en' => 'Boundaries'],
        'slug' => ['en' => 'boundaries'],
    ]);

    foreach ([
        ['title' => 'Title Match', 'excerpt' => 'Nothing special.'],
        ['title' => 'Unrelated', 'excerpt' => 'Excerpt Match.'],
        ['title' => 'Another Article', 'excerpt' => 'Nothing special.', 'tag' => true],
    ] as $index => $attributes) {
        $post = Post::query()->create([
            'title' => $attributes['title'],
            'slug' => str($attributes['title'])->slug(),
            'excerpt' => $attributes['excerpt'],
            'content' => str_repeat('word ', 251),
            'user_id' => $author->getKey(),
            'category_id' => $category->getKey(),
            'status' => PublishStatus::Published,
            'published_at' => now()->subDays($index + 1),
        ]);

        if ($attributes['tag'] ?? false) {
            $post->attachTag($tag);
        }
    }

    $data = blogIndexViewModelData(['q' => 'boundaries']);

    expect($data['posts']->total())->toBe(1)
        ->and($data['posts']->sole()->title)->toBe('Another Article')
        ->and($data['posts']->sole()->content)->toContain('word')
        ->and(PostPresenter::from($data['posts']->sole())->readingTime())->toBe(2)
        ->and($data['seoSource']->robots)->toBe('noindex, follow')
        ->and($data['seoSource']->canonical_url)->toBe(route('blog.index'));

    foreach ([
        'title match' => 'Title Match',
        'excerpt match' => 'Unrelated',
    ] as $term => $expectedTitle) {
        $filtered = blogIndexViewModelData(['q' => $term]);

        expect($filtered['posts']->sole()->title)->toBe($expectedTitle);
    }

    $literal = Post::query()->create([
        'title' => 'Literal %_ Marker',
        'slug' => 'literal-marker',
        'content' => 'A literal search marker.',
        'user_id' => $author->getKey(),
        'category_id' => $category->getKey(),
        'status' => PublishStatus::Published,
        'published_at' => now(),
    ]);

    expect(blogIndexViewModelData(['q' => '%'])['posts']->sole()->is($literal))->toBeTrue()
        ->and(blogIndexViewModelData(['q' => '_'])['posts']->sole()->is($literal))->toBeTrue();
});

/**
 * @param  array{q?: string, category?: string}  $filters
 * @return array<string, mixed>
 */
function blogIndexViewModelData(array $filters = []): array
{
    $query = trim($filters['q'] ?? '');
    $categorySlug = $filters['category'] ?? null;

    return app(BlogIndexViewModel::class)->data(
        app(BlogIndexQuery::class)->results($query, $categorySlug),
        $query,
        $categorySlug,
    );
}

function createBlogIndexViewModelPost(
    User $author,
    Category $category,
    string $title,
    ?DateTimeInterface $publishedAt,
    PublishStatus $status = PublishStatus::Published,
): Post {
    return Post::query()->create([
        'title' => $title,
        'slug' => str($title)->slug(),
        'content' => "{$title} content.",
        'user_id' => $author->getKey(),
        'category_id' => $category->getKey(),
        'status' => $status,
        'published_at' => $publishedAt,
    ]);
}
