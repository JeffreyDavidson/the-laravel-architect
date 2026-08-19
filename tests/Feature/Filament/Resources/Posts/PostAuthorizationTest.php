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

it('allows a reviewer to view and update posts', function () {
    $reviewer = User::factory()->create();
    $reviewer->assignRole('reviewer');

    expect($reviewer->can('viewAny', Post::class))->toBeTrue()
        ->and($reviewer->can('view', $this->post))->toBeTrue()
        ->and($reviewer->can('update', $this->post))->toBeTrue()
        ->and($reviewer->can('create', Post::class))->toBeFalse()
        ->and($reviewer->can('delete', $this->post))->toBeFalse()
        ->and($reviewer->can('deleteAny', Post::class))->toBeFalse()
        ->and($reviewer->can('restore', $this->post))->toBeFalse()
        ->and($reviewer->can('restoreAny', Post::class))->toBeFalse()
        ->and($reviewer->can('forceDelete', $this->post))->toBeFalse()
        ->and($reviewer->can('forceDeleteAny', Post::class))->toBeFalse()
        ->and($reviewer->can('replicate', $this->post))->toBeFalse()
        ->and($reviewer->can('reorder', Post::class))->toBeFalse();

    $this->actingAs($reviewer)
        ->get(PostResource::getUrl('index'))
        ->assertOk();

    $this->get(PostResource::getUrl('edit', ['record' => $this->post]))
        ->assertOk();

    $this->get(PostResource::getUrl('create'))
        ->assertForbidden();
});

it('prevents a basic panel user from managing posts', function () {
    Role::query()->create(['name' => 'panel_user', 'guard_name' => 'web']);
    $panelUser = User::factory()->create();
    $panelUser->assignRole('panel_user');

    expect($panelUser->can('viewAny', Post::class))->toBeFalse()
        ->and($panelUser->can('view', $this->post))->toBeFalse()
        ->and($panelUser->can('create', Post::class))->toBeFalse()
        ->and($panelUser->can('update', $this->post))->toBeFalse();

    $this->actingAs($panelUser)
        ->get(PostResource::getUrl('index'))
        ->assertForbidden();

    $this->get(PostResource::getUrl('edit', ['record' => $this->post]))
        ->assertForbidden();
});
