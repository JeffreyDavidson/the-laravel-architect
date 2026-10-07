<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

it('lists published posts and hides drafts and scheduled posts', function () {
    $published = Post::factory()->published()
        ->create(['title' => 'Published Laravel Architecture']);
    $draft = Post::factory()->create(['title' => 'Draft Laravel Architecture']);
    $scheduled = Post::factory()->published()
        ->create(['title' => 'Scheduled Laravel Architecture', 'published_at' => now()->addDay()]);

    get('/blog')
        ->assertOk()
        ->assertSee($published->title)
        ->assertDontSee($draft->title)
        ->assertDontSee($scheduled->title);
});

it('only allows directly viewing posts that are published now', function () {
    $published = Post::factory()->published()
        ->create();
    $draft = Post::factory()->create();
    $scheduled = Post::factory()->published()
        ->create(['published_at' => now()->addDay()]);

    get(route('blog.show', $published))
        ->assertOk();
    get(route('blog.show', $draft))
        ->assertNotFound();
    get(route('blog.show', $scheduled))
        ->assertNotFound();
});

it('renders post content as markdown with anchored headings', function () {
    $post = Post::factory()->published()
        ->create([
            'content' => <<<'MARKDOWN'
## Native Markdown

This is **rendered** content.

<script>alert('unsafe')</script>

[Unsafe link](javascript:alert('unsafe'))
MARKDOWN,
        ]);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSeeHtml('<h2 id="native-markdown">Native Markdown</h2>')
        ->assertSeeHtml('This is <strong>rendered</strong> content.')
        ->assertDontSeeHtml("<script>alert('unsafe')</script>")
        ->assertDontSeeHtml('javascript:');
});

it('counts only published posts in blog categories', function () {
    $published = Post::factory()->published()
        ->create();
    Post::factory()->create([
        'category_id' => $published->category_id,
    ]);

    get(route('blog.index'))
        ->assertOk()
        ->assertViewHas('categories', function (mixed $categories): bool {
            if (! $categories instanceof Collection) {
                return false;
            }

            $category = $categories->sole();

            return $category instanceof Category && $category->getAttribute('posts_count') === 1;
        });
});

it('shows a post publish date in the display timezone while its metadata keeps the UTC instant', function () {
    config(['app.display_timezone' => 'America/New_York']);
    travelTo('2026-10-10 12:00:00');
    $post = Post::factory()->published()
        ->create(['published_at' => '2026-10-06 01:00:00']);

    get(route('blog.index'))
        ->assertOk()
        ->assertSeeHtml('datetime="2026-10-05"')
        ->assertSee('Oct 05, 2026')
        ->assertDontSee('Oct 06, 2026');

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSeeHtml('<time datetime="2026-10-05">October 05, 2026</time>')
        ->assertSeeHtml('<meta property="article:published_time" content="2026-10-06T01:00:00+00:00">')
        ->assertSeeHtml('"datePublished":"2026-10-06T01:00:00+00:00"');
});
