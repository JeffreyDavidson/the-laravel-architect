<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create();
    $this->category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);
    $this->post = Post::query()->create([
        'title' => 'Authorization boundaries',
        'slug' => 'authorization-boundaries',
        'content' => 'Protected content.',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
    ]);
});

it('allows a super administrator to manage categories', function () {
    $administrator = User::factory()->create(['is_admin' => true]);

    expect($administrator->can('viewAny', Category::class))->toBeTrue()
        ->and($administrator->can('view', $this->category))->toBeTrue()
        ->and($administrator->can('create', Category::class))->toBeTrue()
        ->and($administrator->can('update', $this->category))->toBeTrue()
        ->and($administrator->can('delete', $this->category))->toBeTrue()
        ->and($administrator->can('deleteAny', Category::class))->toBeTrue()
        ->and($administrator->can('restore', $this->category))->toBeTrue()
        ->and($administrator->can('restoreAny', Category::class))->toBeTrue()
        ->and($administrator->can('forceDelete', $this->category))->toBeTrue()
        ->and($administrator->can('forceDeleteAny', Category::class))->toBeTrue()
        ->and($administrator->can('replicate', $this->category))->toBeTrue()
        ->and($administrator->can('reorder', Category::class))->toBeTrue();

    $this->actingAs($administrator)
        ->get(CategoryResource::getUrl('index'))
        ->assertOk();

    livewire(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->assertFormComponentActionVisible('category_id', 'createOption');
});

it('prevents a non-administrator from managing categories', function () {
    $panelUser = User::factory()->create();

    expect($panelUser->can('viewAny', Category::class))->toBeFalse()
        ->and($panelUser->can('view', $this->category))->toBeFalse()
        ->and($panelUser->can('create', Category::class))->toBeFalse()
        ->and($panelUser->can('update', $this->category))->toBeFalse()
        ->and($panelUser->can('delete', $this->category))->toBeFalse();

    $this->actingAs($panelUser)
        ->get(CategoryResource::getUrl('index'))
        ->assertForbidden();
});
