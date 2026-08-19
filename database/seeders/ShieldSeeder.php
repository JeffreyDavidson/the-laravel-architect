<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Database\Seeder;
use LogicException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all resource permissions for super_admin
        $resources = [
            'post', 'category', 'tag', 'project', 'podcast', 'episode',
            'subscriber', 'video', 'testimonial', 'user',
        ];

        $actions = ['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny', 'restore', 'restoreAny', 'replicate', 'reorder'];

        $allPermissions = [];
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $allPermissions[] = $this->permissionName($action, $resource);
            }
        }

        foreach ($actions as $action) {
            $allPermissions[] = $this->permissionName($action, 'role');
        }

        // Also add page/widget permissions
        $allPermissions[] = $this->permissionName('view', 'Shield');
        $allPermissions[] = $this->permissionName('view', 'WelcomeWidget');
        $allPermissions[] = $this->permissionName('view', 'QuickLinksWidget');
        $allPermissions[] = $this->permissionName('view', 'RecentActivityWidget');

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Super admin gets everything
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Reviewer role: can only view and update posts
        $reviewer = Role::firstOrCreate(['name' => 'reviewer', 'guard_name' => 'web']);
        $reviewerPermissions = [
            $this->permissionName('viewAny', 'post'),
            $this->permissionName('view', 'post'),
            $this->permissionName('update', 'post'),
            $this->permissionName('view', 'WelcomeWidget'),
            $this->permissionName('view', 'RecentActivityWidget'),
        ];
        $reviewer->syncPermissions($reviewerPermissions);
    }

    private function permissionName(string $action, string $subject): string
    {
        $separator = config('filament-shield.permissions.separator');
        $case = config('filament-shield.permissions.case');

        if (! is_string($separator) || ! is_string($case)) {
            throw new LogicException('Filament Shield permission formatting must be configured with strings.');
        }

        return FilamentShield::defaultPermissionKeyBuilder(
            affix: $action,
            separator: $separator,
            subject: $subject,
            case: $case,
        );
    }
}
