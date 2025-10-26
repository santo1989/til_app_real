<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Objective;
use Illuminate\Auth\Access\HandlesAuthorization;

class ObjectivePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Super admin has full access
        if ($user->role === 'super_admin') return true;

        // HR admin and leadership can view lists
        return in_array($user->role, ['hr_admin', 'line_manager', 'dept_head', 'board']);
    }

    public function view(User $user, Objective $objective): bool
    {
        // Super admin has full access
        if ($user->role === 'super_admin') return true;

        if ($user->role === 'hr_admin') return true;
        if ($user->role === 'line_manager') {
            // Managers can view their own objectives and their direct reports'
            return $objective->user_id === $user->id
                || ($objective->user && $objective->user->line_manager_id === $user->id);
        }
        if ($user->role === 'dept_head' || $user->role === 'board') {
            // Heads/Board can view but not necessarily edit; keep simple: allow
            return true;
        }
        // employees can view their own objectives
        return $objective->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Super admin has full access
        if ($user->role === 'super_admin') return true;

        // employees and managers and HR can create objectives
        return in_array($user->role, ['employee', 'line_manager', 'hr_admin', 'board']);
    }

    public function update(User $user, Objective $objective): bool
    {
        // Super admin has full access
        if ($user->role === 'super_admin') return true;

        if ($user->role === 'hr_admin') return true;
        if ($user->role === 'line_manager') {
            // manager may update objectives for their team
            return $objective->user && $objective->user->line_manager_id === $user->id;
        }
        // employee can update their own objective
        return $objective->user_id === $user->id;
    }

    public function delete(User $user, Objective $objective): bool
    {
        // Super admin has full access
        if ($user->role === 'super_admin') return true;

        return $user->role === 'hr_admin';
    }
}
