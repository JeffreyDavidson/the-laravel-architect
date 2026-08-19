<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Post');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->can('View:Post');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Post');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->can('Update:Post');
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->can('Delete:Post');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('DeleteAny:Post');
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->can('Restore:Post');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny:Post');
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->can('ForceDelete:Post');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny:Post');
    }

    public function replicate(User $user, Post $post): bool
    {
        return $user->can('Replicate:Post');
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:Post');
    }
}
