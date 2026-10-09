<?php

use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('publishes a post through Filament and exposes it publicly', function () {
    $post = Post::factory()->create(['published_at' => now()->subMinute()]);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Post published');

    expect($post->refresh()
        ->status)->toBe(PublishStatus::Published);

    get(route('blog.show', $post))
        ->assertOk();
    get('/sitemap.xml')
        ->assertSeeHtml(route('blog.show', $post));
});

it('hides a post again when Filament unpublishes it', function () {
    $post = Post::factory()
        ->published()
        ->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified('Post unpublished');

    expect($post->refresh()
        ->status)->toBe(PublishStatus::Draft);

    get(route('blog.show', $post))
        ->assertNotFound();
    get('/sitemap.xml')
        ->assertDontSeeHtml(route('blog.show', $post));
});
