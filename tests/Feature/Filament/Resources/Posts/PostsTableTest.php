<?php

use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use App\Presenters\PostPresenter;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('links the view on site action to the public post URL', function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);

    $post = Post::factory()
        ->published()
        ->create();

    livewire(ListPosts::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($post), route('blog.show', $post))
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($post));
});

it('links the view on site action to a signed preview for a draft', function () {
    freezeSecond();
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);

    $post = Post::factory()->create();

    livewire(ListPosts::class)
        ->assertActionHasUrl(TestAction::make('view_on_site')->table($post), PostPresenter::from($post)->previewUrl())
        ->assertActionShouldOpenUrlInNewTab(TestAction::make('view_on_site')->table($post));
});
