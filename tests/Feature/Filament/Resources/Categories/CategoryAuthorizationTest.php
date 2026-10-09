<?php

use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Categories\CategoryResource;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('allows an administrator to manage categories', function () {
    $category = Category::factory()->create();
    $post = Post::factory()
        ->for($category)
        ->create();
    $administrator = User::factory()->create(['is_admin' => true]);

    expect($administrator->can('viewAny', Category::class))->toBeTrue()
        ->and($administrator->can('view', $category))
        ->toBeTrue()
        ->and($administrator->can('create', Category::class))
        ->toBeTrue()
        ->and($administrator->can('update', $category))
        ->toBeTrue()
        ->and($administrator->can('delete', $category))
        ->toBeTrue()
        ->and($administrator->can('deleteAny', Category::class))
        ->toBeTrue()
        ->and($administrator->can('restore', $category))
        ->toBeTrue()
        ->and($administrator->can('restoreAny', Category::class))
        ->toBeTrue()
        ->and($administrator->can('forceDelete', $category))
        ->toBeTrue()
        ->and($administrator->can('forceDeleteAny', Category::class))
        ->toBeTrue()
        ->and($administrator->can('replicate', $category))
        ->toBeTrue()
        ->and($administrator->can('reorder', Category::class))
        ->toBeTrue();

    actingAs($administrator)
        ->get(CategoryResource::getUrl('index'))
        ->assertOk();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertActionVisible(TestAction::make('createOption')->schemaComponent('category_id'));
});

it('prevents a non-administrator from managing categories', function () {
    $category = Category::factory()->create();
    $panelUser = User::factory()->create();

    expect($panelUser->can('viewAny', Category::class))->toBeFalse()
        ->and($panelUser->can('view', $category))
        ->toBeFalse()
        ->and($panelUser->can('create', Category::class))
        ->toBeFalse()
        ->and($panelUser->can('update', $category))
        ->toBeFalse()
        ->and($panelUser->can('delete', $category))
        ->toBeFalse();

    actingAs($panelUser)
        ->get(CategoryResource::getUrl('index'))
        ->assertForbidden();
});
