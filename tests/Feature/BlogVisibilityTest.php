<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createBlogPost(array $overrides = []): Post
{
    $user = User::query()->create([
        'name' => 'Jeffrey Davidson',
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password'),
    ]);

    $category = Category::query()->firstOrCreate(
        ['slug' => 'laravel'],
        ['name' => 'Laravel'],
    );

    return Post::query()->create(array_merge([
        'title' => fake()->unique()->sentence(3),
        'slug' => fake()->unique()->slug(),
        'excerpt' => fake()->sentence(),
        'content' => fake()->paragraphs(3, true),
        'category_id' => $category->id,
        'user_id' => $user->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ], $overrides));
}

it('lists published posts and hides drafts and scheduled posts', function () {
    $published = createBlogPost(['title' => 'Published Laravel Architecture']);
    $draft = createBlogPost(['title' => 'Draft Laravel Architecture', 'status' => PublishStatus::Draft, 'published_at' => null]);
    $scheduled = createBlogPost(['title' => 'Scheduled Laravel Architecture', 'published_at' => now()->addDay()]);

    $this->get('/blog')
        ->assertOk()
        ->assertSee($published->title)
        ->assertDontSee($draft->title)
        ->assertDontSee($scheduled->title);
});

it('only allows directly viewing posts that are published now', function () {
    $published = createBlogPost();
    $draft = createBlogPost(['status' => PublishStatus::Draft, 'published_at' => null]);
    $scheduled = createBlogPost(['published_at' => now()->addDay()]);

    $this->get(route('blog.show', $published))->assertOk();
    $this->get(route('blog.show', $draft))->assertNotFound();
    $this->get(route('blog.show', $scheduled))->assertNotFound();
});

it('renders post content as markdown with anchored headings', function () {
    $post = createBlogPost([
        'content' => "## Native Markdown\n\nThis is **rendered** content.",
    ]);

    $this->get(route('blog.show', $post))
        ->assertOk()
        ->assertSeeHtml('<h2 id="native-markdown">Native Markdown</h2>')
        ->assertSeeHtml('This is <strong>rendered</strong> content.');
});

it('counts only published posts in blog categories', function () {
    $published = createBlogPost();
    createBlogPost([
        'category_id' => $published->category_id,
        'status' => PublishStatus::Draft,
        'published_at' => null,
    ]);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertViewHas('categories', fn ($categories) => $categories->sole()->posts_count === 1);
});
