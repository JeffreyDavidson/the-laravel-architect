<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all resource permissions for super_admin
        $resources = [
            'post', 'category', 'tag', 'project', 'podcast', 'episode',
            'subscriber', 'video', 'testimonial',
        ];

        $actions = ['view_any', 'view', 'create', 'update', 'delete', 'delete_any', 'force_delete', 'force_delete_any', 'restore', 'restore_any', 'reorder'];

        $allPermissions = [];
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $allPermissions[] = "{$action}_{$resource}";
            }
        }

        // Also add page/widget permissions
        $allPermissions[] = 'page_Shield';
        $allPermissions[] = 'widget_WelcomeWidget';
        $allPermissions[] = 'widget_QuickLinksWidget';
        $allPermissions[] = 'widget_RecentActivityWidget';

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Super admin gets everything
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Reviewer role: can only view and update posts
        $reviewer = Role::firstOrCreate(['name' => 'reviewer', 'guard_name' => 'web']);
        $reviewerPermissions = [
            'view_any_post',
            'view_post',
            'update_post',
            'widget_WelcomeWidget',
            'widget_RecentActivityWidget',
        ];
        $reviewer->syncPermissions($reviewerPermissions);
    }
}
