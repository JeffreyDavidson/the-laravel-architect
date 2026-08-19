<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, Role $role): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, Role $role): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->is_admin;
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function restore(User $user, Role $role): bool
    {
        return $user->is_admin;
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return $user->is_admin;
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function restoreAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function replicate(User $user, Role $role): bool
    {
        return $user->is_admin;
    }

    public function reorder(User $user): bool
    {
        return $user->is_admin;
    }
}
