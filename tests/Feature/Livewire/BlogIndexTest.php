<?php

use App\Enums\PublishStatus;
use App\Livewire\BlogIndex;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

it('updates collection metadata and preserves real pagination links', function () {
    $author = User::factory()->create();
    $category = Category::query()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    foreach (range(1, 13) as $number) {
        createBlogIndexComponentPost($author, $category, "Article {$number}");
    }

    Livewire::test(BlogIndex::class)
        ->assertSeeHtml('href="'.e(route('blog.index', ['page' => 2])).'"')
        ->call('gotoPage', 2)
        ->assertDispatched('blog-metadata-updated', function (string $event, array $data): bool {
            $schemas = $data['structuredData'] ?? null;
            if (! is_array($schemas)) {
                return false;
            }
            $items = array_find($schemas, fn (mixed $schema): bool => is_array($schema) && ($schema['@type'] ?? null) === 'ItemList');

            return $data['canonicalUrl'] === route('blog.index', ['page' => 2])
                && data_get($items, 'itemListElement.0.position') === 13;
        });
});

it('filters published posts by search and category without leaving the component', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);
    $otherCategory = Category::query()->create([
        'name' => 'PHP',
        'slug' => 'php',
    ]);

    createBlogIndexComponentPost($author, $category, 'Laravel Architecture');
    createBlogIndexComponentPost($author, $otherCategory, 'PHP Architecture');

    Livewire::test(BlogIndex::class)
        ->set('search', 'Laravel')
        ->assertSet('search', 'Laravel')
        ->assertSee('Laravel Architecture')
        ->assertDontSee('PHP Architecture')
        ->call('selectCategory', 'php')
        ->assertSet('categorySlug', 'php')
        ->assertSee('No matching articles');
});

it('clears the interactive blog filters', function () {
    $author = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    createBlogIndexComponentPost($author, $category, 'Laravel Architecture');

    Livewire::test(BlogIndex::class, ['search' => 'Laravel', 'categorySlug' => 'laravel'])
        ->assertSet('search', 'Laravel')
        ->assertSet('categorySlug', 'laravel')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('categorySlug', null)
        ->assertSee('Laravel Architecture');
});

function createBlogIndexComponentPost(User $author, Category $category, string $title): Post
{
    return Post::query()->create([
        'title' => $title,
        'slug' => str($title)->slug(),
        'content' => "{$title} content.",
        'user_id' => $author->getKey(),
        'category_id' => $category->getKey(),
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);
}
