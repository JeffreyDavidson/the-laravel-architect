<?php

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an administrator to manage users', function () {
    $administrator = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create();

    $this->actingAs($administrator)
        ->get(UserResource::getUrl('index'))
        ->assertOk();

    $this->get(UserResource::getUrl('create'))
        ->assertOk();

    $this->get(UserResource::getUrl('edit', ['record' => $user]))
        ->assertOk();
});

it('prevents a non-administrator from managing users', function () {
    $panelUser = User::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($panelUser)
        ->get(UserResource::getUrl('index'))
        ->assertForbidden();

    $this->get(UserResource::getUrl('create'))
        ->assertForbidden();

    $this->get(UserResource::getUrl('edit', ['record' => $user]))
        ->assertForbidden();
});
