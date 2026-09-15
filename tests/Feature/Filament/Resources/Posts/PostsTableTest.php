<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('links the view on site action to the public post URL', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);

    $post = Post::query()->create([
        'title' => 'Filament table actions',
        'slug' => 'filament-table-actions',
        'content' => 'Post content',
        'user_id' => $user->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    livewire(ListPosts::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($post), route('blog.show', $post))
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($post));
});

it('links the view on site action to a signed preview for a draft', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);

    $post = Post::query()->create([
        'title' => 'Filament draft preview',
        'slug' => 'filament-draft-preview',
        'content' => 'Draft post content',
        'user_id' => $user->id,
        'status' => PublishStatus::Draft,
    ]);

    livewire(ListPosts::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($post), app(PreviewUrlGenerator::class)->for($post))
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($post));
});
