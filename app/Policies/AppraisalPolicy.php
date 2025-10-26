<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appraisal;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppraisalPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Appraisal $appraisal)
    {
        if ($user->isSuperAdmin() || $user->isHrAdmin()) {
            return true;
        }

        if ($user->id === $appraisal->user_id) return true;

        if ($user->id === ($appraisal->user->line_manager_id ?? null)) return true;

        $manager = $appraisal->user->lineManager ?? null;
        if ($manager && $user->id === ($manager->line_manager_id ?? null)) return true;

        return false;
    }

    /**
     * Determine whether the user may sign an appraisal with a given role.
     * Usage: $user->can('sign', [$appraisal, $role])
     */
    public function sign(User $user, Appraisal $appraisal, $role)
    {
        if ($user->isSuperAdmin() || $user->isHrAdmin()) return true;

        if ($role === 'employee' && $user->id === $appraisal->user_id) return true;
        if ($role === 'manager' && $user->id === ($appraisal->user->line_manager_id ?? null)) return true;

        if ($role === 'supervisor') {
            $manager = $appraisal->user->lineManager ?? null;
            if ($manager && $user->id === ($manager->line_manager_id ?? null)) return true;
        }

        return false;
    }

    public function approve(User $user, Appraisal $appraisal)
    {
        if ($user->isSuperAdmin()) return true;
        return in_array($user->role, ['dept_head', 'hr_admin']);
    }
}
