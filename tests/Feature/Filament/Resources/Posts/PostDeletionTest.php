<?php

use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
    Storage::fake('public');
});

it('permanently deletes a trashed post and its featured image through the Filament action', function () {
    Storage::disk('public')->put('posts/featured.png', 'image');

    $post = Post::factory()->create(['featured_image_path' => 'posts/featured.png']);

    $post->delete();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction('forceDelete');

    expect(Post::withTrashed()->find($post->id))->toBeNull();
    Storage::disk('public')->assertMissing('posts/featured.png');
});
