<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
    Storage::fake('public');
});

it('deletes a post and its featured image through the Filament action', function () {
    Storage::disk('public')->put('posts/featured.png', 'image');

    $post = Post::query()->create([
        'title' => 'Post to delete',
        'slug' => 'post-to-delete',
        'content' => 'Post content.',
        'user_id' => auth()->id(),
        'status' => PublishStatus::Draft,
        'featured_image_path' => 'posts/featured.png',
    ]);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction('delete');

    expect(Post::query()->find($post->id))->toBeNull();
    Storage::disk('public')->assertMissing('posts/featured.png');
});
