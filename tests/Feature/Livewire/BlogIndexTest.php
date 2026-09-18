<?php

use App\Enums\PublishStatus;
use App\Livewire\BlogIndex;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

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
