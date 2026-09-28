<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('publishes a post through Filament and exposes it publicly', function () {
    $post = Post::query()->create([
        'title' => 'Publishing workflow',
        'slug' => 'publishing-workflow',
        'excerpt' => 'A summary.',
        'content' => 'Published content.',
        'category_id' => Category::query()
            ->create(['name' => 'Laravel', 'slug' => 'laravel'])
            ->id,
        'user_id' => auth()->id(),
        'status' => PublishStatus::Draft,
        'published_at' => now()->subMinute(),
    ]);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Post published');

    expect($post->refresh()
        ->status)->toBe(PublishStatus::Published);

    $this->get(route('blog.show', $post))
        ->assertOk();
    $this->get('/sitemap.xml')
        ->assertSeeHtml(route('blog.show', $post));
});

it('hides a post again when Filament unpublishes it', function () {
    $post = Post::query()->create([
        'title' => 'Draft workflow',
        'slug' => 'draft-workflow',
        'content' => 'Draft content.',
        'user_id' => auth()->id(),
        'status' => PublishStatus::Published,
        'published_at' => now()->subMinute(),
    ]);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified('Post unpublished');

    expect($post->refresh()
        ->status)->toBe(PublishStatus::Draft);

    $this->get(route('blog.show', $post))
        ->assertNotFound();
    $this->get('/sitemap.xml')
        ->assertDontSeeHtml(route('blog.show', $post));
});
