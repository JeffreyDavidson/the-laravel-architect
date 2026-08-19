<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $resources = [
            'post', 'category', 'tag', 'project', 'podcast', 'episode',
            'subscriber', 'video', 'testimonial', 'user',
        ];
        $actions = [
            'view_any' => 'ViewAny',
            'view' => 'View',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
            'delete_any' => 'DeleteAny',
            'force_delete' => 'ForceDelete',
            'force_delete_any' => 'ForceDeleteAny',
            'restore' => 'Restore',
            'restore_any' => 'RestoreAny',
            'replicate' => 'Replicate',
            'reorder' => 'Reorder',
        ];

        DB::transaction(function () use ($resources, $actions): void {
            foreach ($resources as $resource) {
                foreach ($actions as $legacyAction => $action) {
                    $this->replacePermission(
                        "{$legacyAction}_{$resource}",
                        "{$action}:".ucfirst($resource),
                    );
                }
            }

            foreach ($actions as $action) {
                $this->ensurePermission("{$action}:Role");
            }

            $this->replacePermission('page_Shield', 'View:Shield');
            $this->replacePermission('widget_WelcomeWidget', 'View:WelcomeWidget');
            $this->replacePermission('widget_QuickLinksWidget', 'View:QuickLinksWidget');
            $this->replacePermission('widget_RecentActivityWidget', 'View:RecentActivityWidget');

            $superAdminId = DB::table('roles')
                ->where('name', 'super_admin')
                ->where('guard_name', 'web')
                ->value('id');

            if ($superAdminId === null) {
                return;
            }

            $permissionIds = DB::table('permissions')
                ->where('guard_name', 'web')
                ->pluck('id');

            foreach ($permissionIds as $permissionId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $superAdminId,
                ]);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function replacePermission(string $legacyName, string $name): void
    {
        $legacyPermission = DB::table('permissions')
            ->where('name', $legacyName)
            ->where('guard_name', 'web')
            ->first();

        if ($legacyPermission === null) {
            $this->ensurePermission($name);

            return;
        }

        $permission = DB::table('permissions')
            ->where('name', $name)
            ->where('guard_name', 'web')
            ->first();

        if ($permission === null) {
            DB::table('permissions')
                ->where('id', $legacyPermission->id)
                ->update(['name' => $name, 'updated_at' => now()]);

            return;
        }

        foreach (['role_has_permissions', 'model_has_permissions'] as $table) {
            $assignments = DB::table($table)
                ->where('permission_id', $legacyPermission->id)
                ->get();

            foreach ($assignments as $assignment) {
                $values = (array) $assignment;
                $values['permission_id'] = $permission->id;
                DB::table($table)->insertOrIgnore($values);
            }

            DB::table($table)
                ->where('permission_id', $legacyPermission->id)
                ->delete();
        }

        DB::table('permissions')
            ->where('id', $legacyPermission->id)
            ->delete();
    }

    private function ensurePermission(string $name): void
    {
        DB::table('permissions')->insertOrIgnore([
            'name' => $name,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
