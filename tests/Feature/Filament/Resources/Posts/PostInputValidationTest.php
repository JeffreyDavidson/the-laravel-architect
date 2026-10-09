<?php

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('rejects post titles longer than the database column', function () {
    actingAs(User::factory()->create(['is_admin' => true]));

    livewire(CreatePost::class)
        ->fillForm([
            'title' => str_repeat('a', 256),
            'slug' => 'oversized-post-title',
            'content' => 'Post content',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'max']);

    expect(Post::query()->exists())->toBeFalse();
});
