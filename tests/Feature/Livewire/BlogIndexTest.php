<?php

use App\Livewire\BlogIndex;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('updates collection metadata and preserves real pagination links', function () {
    $category = Category::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    Post::factory()->count(13)
        ->for($category)
        ->published()
        ->create();

    livewire(BlogIndex::class)
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
    $category = Category::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);
    $otherCategory = Category::factory()->create([
        'name' => 'PHP',
        'slug' => 'php',
    ]);

    Post::factory()->for($category)
        ->published()
        ->create(['title' => 'Laravel Architecture']);
    Post::factory()->for($otherCategory)
        ->published()
        ->create(['title' => 'PHP Architecture']);

    livewire(BlogIndex::class)
        ->set('search', 'Laravel')
        ->assertSet('search', 'Laravel')
        ->assertSee('Laravel Architecture')
        ->assertDontSee('PHP Architecture')
        ->call('selectCategory', 'php')
        ->assertSet('categorySlug', 'php')
        ->assertSee('No matching articles');
});

it('renders each category pill with a compiled selectCategory call', function () {
    $category = Category::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    Post::factory()->for($category)
        ->published()
        ->create(['title' => 'Laravel Architecture']);

    livewire(BlogIndex::class)
        ->assertDontSeeHtml('@js(')
        ->assertSeeHtml('wire:click.prevent="selectCategory(\'laravel\')"');
});

it('clears the interactive blog filters', function () {
    $category = Category::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    Post::factory()->for($category)
        ->published()
        ->create(['title' => 'Laravel Architecture']);

    livewire(BlogIndex::class, ['search' => 'Laravel', 'categorySlug' => 'laravel'])
        ->assertSet('search', 'Laravel')
        ->assertSet('categorySlug', 'laravel')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('categorySlug', null)
        ->assertSee('Laravel Architecture');
});
