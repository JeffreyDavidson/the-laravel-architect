<?php

use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('links the view on site action to the public post URL', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);

    $post = Post::query()->create([
        'title' => 'Filament table actions',
        'slug' => 'filament-table-actions',
        'content' => 'Post content',
        'user_id' => $user->id,
    ]);

    livewire(ListPosts::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($post), route('blog.show', $post))
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($post));
});
