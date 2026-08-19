<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('only admits super administrators to the admin panel', function () {
    $user = User::factory()->create();
    $panel = Filament::getPanel('admin');

    expect($user->canAccessPanel($panel))->toBeFalse();

    Role::query()->create(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole('super_admin');

    expect($user->canAccessPanel($panel))->toBeFalse();

    $user->forceFill(['is_admin' => true])->save();

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('rejects non-administrator roles at the admin panel boundary', function (string $role) {
    Role::query()->create(['name' => $role, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);
    $panel = Filament::getPanel('admin');

    expect($user->canAccessPanel($panel))->toBeFalse();

    $this->actingAs($user)
        ->get($panel->getUrl())
        ->assertForbidden();
})->with(['reviewer', 'panel_user']);
