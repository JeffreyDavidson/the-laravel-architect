<?php

use App\Models\Category;
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
