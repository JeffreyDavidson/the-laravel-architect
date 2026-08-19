<?php

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Database\Seeders\ShieldSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ShieldSeeder::class);
});

it('seeds permissions using the configured Shield naming convention', function () {
    expect(Permission::query()->where('name', 'ViewAny:User')->exists())->toBeTrue()
        ->and(Permission::query()->where('name', 'Replicate:User')->exists())->toBeTrue()
        ->and(Permission::query()->where('name', 'ViewAny:Role')->exists())->toBeTrue()
        ->and(Permission::query()->where('name', 'View:WelcomeWidget')->exists())->toBeTrue()
        ->and(Permission::query()->where('name', 'view_any_user')->exists())->toBeFalse();
});

it('migrates legacy permissions without losing role assignments', function () {
    $legacyPermission = Permission::query()->create([
        'name' => 'view_any_user',
        'guard_name' => 'web',
    ]);
    $role = Role::query()->create([
        'name' => 'legacy-role',
        'guard_name' => 'web',
    ]);
    $role->givePermissionTo($legacyPermission);

    $migration = require database_path('migrations/2026_08_18_120400_align_filament_shield_permissions.php');
    $migration->up();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect(Permission::query()->where('name', 'view_any_user')->exists())->toBeFalse()
        ->and($role->fresh()->hasPermissionTo('ViewAny:User'))->toBeTrue();
});

it('does not register role management in the admin panel', function () {
    expect(Filament::getPanel('admin')->getResources())
        ->not->toContain(RoleResource::class);
});
