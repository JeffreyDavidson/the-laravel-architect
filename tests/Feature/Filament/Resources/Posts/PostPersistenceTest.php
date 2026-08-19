<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
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
        ->and(Post::query()->sole()->user_id)->toBe(auth()->id());
});

it('updates a post through the Filament form', function () {
    $post = Post::query()->create([
        'title' => 'Original title',
        'slug' => 'original-title',
        'content' => 'Original content.',
        'user_id' => auth()->id(),
        'status' => PublishStatus::Draft,
    ]);

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
        ->and($post->content)->toBe('Updated content.')
        ->and($post->status)->toBe(PublishStatus::InReview);
});
