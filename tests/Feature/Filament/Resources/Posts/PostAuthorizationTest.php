<?php

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ShieldSeeder::class);

    $this->author = User::factory()->create();
    $this->post = Post::query()->create([
        'title' => 'Authorization boundaries',
        'slug' => 'authorization-boundaries',
        'content' => 'Protected content.',
        'user_id' => $this->author->id,
    ]);
});

it('allows a super administrator to manage posts', function () {
    $administrator = User::factory()->create();
    $administrator->assignRole('super_admin');

    expect($administrator->can('viewAny', Post::class))->toBeTrue()
        ->and($administrator->can('view', $this->post))->toBeTrue()
        ->and($administrator->can('create', Post::class))->toBeTrue()
        ->and($administrator->can('update', $this->post))->toBeTrue()
        ->and($administrator->can('delete', $this->post))->toBeTrue()
        ->and($administrator->can('deleteAny', Post::class))->toBeTrue()
        ->and($administrator->can('restore', $this->post))->toBeTrue()
        ->and($administrator->can('restoreAny', Post::class))->toBeTrue()
        ->and($administrator->can('forceDelete', $this->post))->toBeTrue()
        ->and($administrator->can('forceDeleteAny', Post::class))->toBeTrue()
        ->and($administrator->can('replicate', $this->post))->toBeTrue()
        ->and($administrator->can('reorder', Post::class))->toBeTrue();
});

it('prevents non-administrator roles from managing posts', function (string $role) {
    Role::query()->create(['name' => $role, 'guard_name' => 'web']);
    $panelUser = User::factory()->create();
    $panelUser->assignRole($role);

    expect($panelUser->can('viewAny', Post::class))->toBeFalse()
        ->and($panelUser->can('view', $this->post))->toBeFalse()
        ->and($panelUser->can('create', Post::class))->toBeFalse()
        ->and($panelUser->can('update', $this->post))->toBeFalse();

    $this->actingAs($panelUser)
        ->get(PostResource::getUrl('index'))
        ->assertForbidden();

    $this->get(PostResource::getUrl('edit', ['record' => $this->post]))
        ->assertForbidden();
})->with(['reviewer', 'panel_user']);
