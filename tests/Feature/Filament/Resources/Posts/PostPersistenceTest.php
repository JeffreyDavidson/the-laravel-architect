<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\CreatePost;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\EditPost;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('creates a post through the Filament form', function () {
    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'Architecture notes',
            'slug' => 'architecture-notes',
            'excerpt' => 'A short summary.',
            'content' => 'The full post content.',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->sole())
        ->title->toBe('Architecture notes')
        ->and(Post::query()->sole()
            ->user_id)
        ->toBe(auth()->id());
});

it('updates a post through the Filament form', function () {
    $post = Post::factory()->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'title' => 'Updated title',
            'content' => 'Updated content.',
            'status' => PublishStatus::InReview,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->refresh())
        ->title->toBe('Updated title')
        ->and($post->content)
        ->toBe('Updated content.')
        ->and($post->status)
        ->toBe(PublishStatus::InReview);
});

it('links and unlinks related episodes through the Filament form', function () {
    $episodeIds = Episode::factory()
        ->count(2)
        ->recycle(Podcast::factory()->create())
        ->create()
        ->pluck('id')
        ->all();
    $post = Post::factory()->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['episodes' => $episodeIds])
        ->call('save')
        ->assertHasNoFormErrors();

    $post->refresh();

    expect($post->episodes)
        ->toHaveCount(2);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['episodes' => []])
        ->call('save')
        ->assertHasNoFormErrors();

    $post->refresh();

    expect($post->episodes)
        ->toBeEmpty();
});
