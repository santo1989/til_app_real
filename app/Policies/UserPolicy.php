<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the current user can view the given user profile.
     */
    public function view(User $current, User $user)
    {
        // super admin and hr admin can view all
        if (in_array($current->role, ['super_admin', 'hr_admin'])) return true;
        // self
        if ($current->id === $user->id) return true;
        // line manager
        if ($user->line_manager_id && $current->id === $user->line_manager_id) return true;
        return false;
    }

    /**
     * Determine whether the current user can view confidential details of the given user
     * such as `password_plain`.
     * Only HR admins, Super Admins, and the user themselves are allowed.
     */
    public function viewConfidential(User $current, User $user)
    {
        if (in_array($current->role, ['super_admin', 'hr_admin'])) return true;
        if ($current->id === $user->id) return true;
        return false;
    }
}
