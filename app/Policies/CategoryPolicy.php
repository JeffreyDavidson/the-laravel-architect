<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Category');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->can('View:Category');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Category');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('Update:Category');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can('Delete:Category');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('DeleteAny:Category');
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->can('Restore:Category');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny:Category');
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->can('ForceDelete:Category');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny:Category');
    }

    public function replicate(User $user, Category $category): bool
    {
        return $user->can('Replicate:Category');
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:Category');
    }
}
