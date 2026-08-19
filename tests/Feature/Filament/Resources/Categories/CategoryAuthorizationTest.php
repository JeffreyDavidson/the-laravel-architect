<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ShieldSeeder::class);

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
    $administrator = User::factory()->create();
    $administrator->assignRole('super_admin');

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

    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->assertFormComponentActionVisible('category_id', 'createOption');
});

it('prevents non-administrator panel roles from managing categories', function (string $role) {
    Role::query()->firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    $panelUser = User::factory()->create();
    $panelUser->assignRole($role);

    expect($panelUser->can('viewAny', Category::class))->toBeFalse()
        ->and($panelUser->can('view', $this->category))->toBeFalse()
        ->and($panelUser->can('create', Category::class))->toBeFalse()
        ->and($panelUser->can('update', $this->category))->toBeFalse()
        ->and($panelUser->can('delete', $this->category))->toBeFalse();

    $this->actingAs($panelUser)
        ->get(CategoryResource::getUrl('index'))
        ->assertForbidden();
})->with(['reviewer', 'panel_user']);

it('prevents a reviewer from creating categories through the post form', function () {
    $reviewer = User::factory()->create();
    $reviewer->assignRole('reviewer');
    $this->actingAs($reviewer);

    Livewire::test(EditPost::class, ['record' => $this->post->getRouteKey()])
        ->assertFormComponentActionHidden('category_id', 'createOption');
});
