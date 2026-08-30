<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('rejects post titles longer than the database column', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

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
