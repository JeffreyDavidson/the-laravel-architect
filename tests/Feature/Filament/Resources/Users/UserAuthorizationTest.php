<?php

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ShieldSeeder::class);
});

it('allows a super administrator to manage users', function () {
    $administrator = User::factory()->create();
    $administrator->assignRole('super_admin');
    $user = User::factory()->create();

    $this->actingAs($administrator)
        ->get(UserResource::getUrl('index'))
        ->assertOk();

    $this->get(UserResource::getUrl('create'))
        ->assertOk();

    $this->get(UserResource::getUrl('edit', ['record' => $user]))
        ->assertOk();
});

it('prevents non-administrator panel roles from managing users', function (string $role) {
    Role::query()->firstOrCreate(['name' => $role, 'guard_name' => 'web']);

    $panelUser = User::factory()->create();
    $panelUser->assignRole($role);
    $user = User::factory()->create();

    $this->actingAs($panelUser)
        ->get(UserResource::getUrl('index'))
        ->assertForbidden();

    $this->get(UserResource::getUrl('create'))
        ->assertForbidden();

    $this->get(UserResource::getUrl('edit', ['record' => $user]))
        ->assertForbidden();
})->with(['reviewer', 'panel_user']);

it('does not expose role assignment in user management', function () {
    $administrator = User::factory()->create();
    $administrator->assignRole('super_admin');
    $this->actingAs($administrator);

    livewire(EditUser::class, ['record' => $administrator->getRouteKey()])
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('email')
        ->assertFormFieldDoesNotExist('roles');
});
