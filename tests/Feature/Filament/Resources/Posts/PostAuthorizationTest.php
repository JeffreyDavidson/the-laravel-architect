<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\PostResource;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('allows an administrator to manage posts', function () {
    $post = Post::factory()->create();
    $administrator = User::factory()->create(['is_admin' => true]);

    expect($administrator->can('viewAny', Post::class))->toBeTrue()
        ->and($administrator->can('view', $post))
        ->toBeTrue()
        ->and($administrator->can('create', Post::class))
        ->toBeTrue()
        ->and($administrator->can('update', $post))
        ->toBeTrue()
        ->and($administrator->can('delete', $post))
        ->toBeTrue()
        ->and($administrator->can('deleteAny', Post::class))
        ->toBeTrue()
        ->and($administrator->can('restore', $post))
        ->toBeTrue()
        ->and($administrator->can('restoreAny', Post::class))
        ->toBeTrue()
        ->and($administrator->can('forceDelete', $post))
        ->toBeTrue()
        ->and($administrator->can('forceDeleteAny', Post::class))
        ->toBeTrue()
        ->and($administrator->can('replicate', $post))
        ->toBeTrue()
        ->and($administrator->can('reorder', Post::class))
        ->toBeTrue();
});

it('prevents a non-administrator from managing posts', function () {
    $post = Post::factory()->create();
    $panelUser = User::factory()->create();

    expect($panelUser->can('viewAny', Post::class))->toBeFalse()
        ->and($panelUser->can('view', $post))
        ->toBeFalse()
        ->and($panelUser->can('create', Post::class))
        ->toBeFalse()
        ->and($panelUser->can('update', $post))
        ->toBeFalse();

    actingAs($panelUser)
        ->get(PostResource::getUrl('index'))
        ->assertForbidden();

    get(PostResource::getUrl('edit', ['record' => $post]))
        ->assertForbidden();
});
