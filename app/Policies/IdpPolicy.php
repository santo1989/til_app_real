<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Idp;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdpPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Idp $idp): bool
    {
        // Super admin has full access
        if ($user->role === 'super_admin') return true;

        if ($user->role === 'hr_admin') return true;
        if ($user->role === 'line_manager') {
            return $idp->user && $idp->user->line_manager_id === $user->id;
        }
        return $idp->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Super admin has full access
        if ($user->role === 'super_admin') return true;

        return in_array($user->role, ['employee', 'line_manager', 'hr_admin']);
    }

    public function update(User $user, Idp $idp): bool
    {
        // Super admin has full access
        if ($user->role === 'super_admin') return true;

        if ($user->role === 'hr_admin') return true;
        if ($user->role === 'line_manager') {
            return $idp->user && $idp->user->line_manager_id === $user->id;
        }
        return $idp->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the given IDP.
     * Default to the same rules as update: HR and super admin may delete,
     * line managers may delete IDPs for their reports, and owners may delete their own.
     */
    public function delete(User $user, Idp $idp): bool
    {
        // Only HR admins and Super Admins may delete IDPs. This tightens
        // destructive permissions to a small set of trusted roles.
        return in_array($user->role, ['super_admin', 'hr_admin'], true);
    }
}
