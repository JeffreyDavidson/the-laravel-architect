<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('publishes a post through Filament and exposes it publicly', function () {
    $post = Post::query()->create([
        'title' => 'Publishing workflow',
        'slug' => 'publishing-workflow',
        'content' => 'Published content.',
        'user_id' => auth()->id(),
        'status' => PublishStatus::Draft,
    ]);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'status' => PublishStatus::Published,
            'published_at' => now()->subMinute(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->refresh()->status)->toBe(PublishStatus::Published);

    $this->get(route('blog.show', $post))->assertOk();
    $this->get('/sitemap.xml')->assertSee(route('blog.show', $post), false);
});

it('hides a post again when Filament changes it back to draft', function () {
    $post = Post::query()->create([
        'title' => 'Draft workflow',
        'slug' => 'draft-workflow',
        'content' => 'Draft content.',
        'user_id' => auth()->id(),
        'status' => PublishStatus::Published,
        'published_at' => now()->subMinute(),
    ]);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['status' => PublishStatus::Draft, 'published_at' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->refresh()->status)->toBe(PublishStatus::Draft);

    $this->get(route('blog.show', $post))->assertNotFound();
    $this->get('/sitemap.xml')->assertDontSee(route('blog.show', $post), false);
});
