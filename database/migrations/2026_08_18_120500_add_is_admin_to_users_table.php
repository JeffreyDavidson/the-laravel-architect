<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_admin')->default(false)->after('password');
        });

        $superAdminRoleId = DB::table('roles')
            ->where('name', 'super_admin')
            ->where('guard_name', 'web')
            ->value('id');

        if ($superAdminRoleId === null) {
            return;
        }

        $administratorIds = DB::table('model_has_roles')
            ->where('role_id', $superAdminRoleId)
            ->where('model_type', User::class)
            ->pluck('model_id');

        DB::table('users')
            ->whereIn('id', $administratorIds)
            ->update(['is_admin' => true]);
    }
};
