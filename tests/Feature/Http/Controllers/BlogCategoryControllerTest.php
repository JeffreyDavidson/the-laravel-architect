<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../../Support/PublicPageAssertions.php';

it('gives tag and category archives with the same name distinct titles on blog.category', function () {
    $category = Category::factory()->create(['name' => 'Laravel']);
    $tag = Tag::factory()->create(['name' => 'Laravel']);
    $response = get(route('blog.category', $category));
    $response->assertOk()
        ->assertSeeHtml('<title>Laravel Articles — Jeffrey Davidson</title>');
});

it('lists only the category\'s published posts on blog.category', function () {
    // Arrange
    $category = Category::factory()->create(['name' => 'Architecture']);
    $otherCategory = Category::factory()->create(['name' => 'Testing']);
    Post::factory()
        ->for($category)
        ->published()
        ->create(['title' => 'Listed published post']);
    Post::factory()
        ->for($category)
        ->create(['title' => 'Hidden draft post']);
    Post::factory()
        ->for($category)
        ->scheduled()
        ->create(['title' => 'Hidden scheduled post']);
    Post::factory()
        ->for($otherCategory)
        ->published()
        ->create(['title' => 'Post in another category']);

    // Act
    $response = get(route('blog.category', $category));

    // Assert
    $response->assertOk()
        ->assertSee('Listed published post')
        ->assertDontSee('Hidden draft post')
        ->assertDontSee('Hidden scheduled post')
        ->assertDontSee('Post in another category');
});

it('renders the category description on blog.category', function () {
    // Arrange
    $category = Category::factory()->create(['description' => 'Articles about application architecture.']);

    // Act
    $response = get(route('blog.category', $category));

    // Assert
    $response->assertOk()
        ->assertSee('Articles about application architecture.');
});

it('returns not found for an unknown category on blog.category', function () {
    // Act
    $response = get(route('blog.category', 'no-such-category'));

    // Assert
    $response->assertNotFound();
});
