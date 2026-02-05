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
        if ($current->isHrAdmin()) return true;
        // self
        if ($current->id === $user->id) return true;
        // line manager
        if ($user->line_manager_id && $current->id === $user->line_manager_id) return true;
        return false;
    }

    /**
     * Determine whether the current user can view any users (list all users).
     */
    public function viewAny(User $current)
    {
        return $current->isHrAdmin();
    }

    /**
     * Determine whether the current user can view confidential details of the given user
     * such as `password_plain`.
     * Only HR admins, Super Admins, and the user themselves are allowed.
     */
    public function viewConfidential(User $current, User $user)
    {
        if ($current->isHrAdmin()) return true;
        if ($current->id === $user->id) return true;
        return false;
    }

    /**
     * Determine whether the current user can impersonate the given target user.
     */
    public function impersonate(User $current, User $target)
    {
        // Only super admin may impersonate, and cannot impersonate another super_admin
        if (! $current->isSuperAdmin()) return false;
        if ($target->isSuperAdmin()) return false;
        return true;
    }

    /**
     * Determine whether the current user can view midterm progress for the target user.
     */
    public function viewMidterm(User $current, User $target)
    {
        if ($current->isHrAdmin()) return true;
        if ($current->id === $target->id) return true;
        if ($current->role === 'line_manager' && $target->line_manager_id === $current->id) return true;
        return false;
    }

    /**
     * Whether the given user may manage objectives for the target employee (line manager flows).
     */
    public function manageObjectivesFor(User $current, User $employee)
    {
        if ($current->isSuperAdmin()) return true;
        if ($current->isHrAdmin()) return true;
        if ($current->isLineManager() && $employee->line_manager_id === $current->id) return true;
        return false;
    }
}
