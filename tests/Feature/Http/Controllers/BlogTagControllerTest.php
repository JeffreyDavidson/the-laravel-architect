<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

require_once __DIR__.'/../../../Support/PublicPageAssertions.php';

it('gives tag and category archives with the same name distinct titles on blog.tag', function () {
    $category = Category::factory()->create(['name' => 'Laravel']);
    $tag = Tag::factory()->create(['name' => 'Laravel']);
    $response = get(route('blog.tag', $tag));
    $response->assertOk()
        ->assertSeeHtml('<title>Articles Tagged Laravel — Jeffrey Davidson</title>');
});

it('lists only the tag\'s published posts on blog.tag', function () {
    // Arrange
    $tag = Tag::factory()->create(['name' => 'Architecture']);
    $otherTag = Tag::factory()->create(['name' => 'Testing']);
    Post::factory()
        ->published()
        ->create(['title' => 'Listed published post'])
        ->attachTag($tag);
    Post::factory()
        ->create(['title' => 'Hidden draft post'])
        ->attachTag($tag);
    Post::factory()
        ->scheduled()
        ->create(['title' => 'Hidden scheduled post'])
        ->attachTag($tag);
    Post::factory()
        ->published()
        ->create(['title' => 'Post with another tag'])
        ->attachTag($otherTag);

    // Act
    $response = get(route('blog.tag', $tag));

    // Assert
    $response->assertOk()
        ->assertSee('Listed published post')
        ->assertDontSee('Hidden draft post')
        ->assertDontSee('Hidden scheduled post')
        ->assertDontSee('Post with another tag');
});

it('returns not found for an unknown tag on blog.tag', function () {
    // Act
    $response = get(route('blog.tag', 'no-such-tag'));

    // Assert
    $response->assertNotFound();
});
