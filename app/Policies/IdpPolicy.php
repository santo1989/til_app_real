<?php

namespace App\Policies;

use App\Models\Idp;
use App\Models\User;
use App\Models\FinancialYear;
use App\Services\FinancialYearService;
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

class IdpPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Idp $idp)
    {
        if ($user->isHrAdmin()) {
            return true;
        }
        if ($user->id === $idp->user_id) {
            return true;
        }
        if ($user->role === 'line_manager' && isset($idp->user) && $idp->user->line_manager_id === $user->id) {
            return true;
        }
        // Allow employees to view departmental IDPs (team-level visibility)
        if (isset($idp->user) && $idp->user->department_id && $user->department_id && $idp->user->department_id === $user->department_id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view any IDP (admin listing).
     */
    public function viewAny(User $user)
    {
        // Only HR admin and Super admin can view the full IDP listing
        return $user->isHrAdmin();
    }

    public function create(User $user)
    {
        // Allow all authenticated users to create an IDP; business rules around approval
        // are handled elsewhere (controller sets is_approved).
        return in_array($user->role, ['employee', 'line_manager', 'hr_admin', 'super_admin']);
    }

    public function update(User $user, Idp $idp)
    {
        // If IDP already approved, only HR or Super Admin may edit
        if (isset($idp->is_approved) && $idp->is_approved) {
            return $user->isHrAdmin() || $user->isSuperAdmin();
        }

        // Allow HR admins to always edit
        if ($user->isHrAdmin()) {
            return true;
        }

        // Determine financial year windows
        $activeFy = FinancialYear::getActive();
        $fyService = new FinancialYearService($activeFy);
        $now = Carbon::now();
        $beforeMidterm = $activeFy ? $now->lte($fyService->midtermDate()) : true;
        $beforeNinth = $activeFy ? $now->lte($fyService->ninthMonthCutoff()) : true;

        if ($user->isLineManager()) {
            // Line managers can update their own IDPs and IDPs of their direct reports
            if ($idp->user_id === $user->id) return $beforeNinth;
            if (isset($idp->user) && $idp->user->line_manager_id === $user->id) {
                // For team-level or department-level IDP edits, require HR approval prior to manager edits
                if (!empty($idp->approved_by_id)) {
                    $approver = $idp->approver ?? null;
                    if ($approver && $approver->role === 'hr_admin') {
                        return $beforeNinth;
                    }
                    // if approver exists but not HR, deny manager edits
                    return false;
                }
                // if not approved yet, allow manager to edit until cutoff
                return $beforeNinth;
            }
            return false;
        }

        if ($user->role === 'employee') {
            // Employees can update only their own IDPs and only after line-manager approval and before 9th month
            if ($idp->user_id !== $user->id) return false;
            if (empty($idp->approved_by_id)) return false;
            return $beforeNinth;
        }

        return false;
    }

    public function delete(User $user, Idp $idp)
    {
        if ($user->isHrAdmin()) {
            return true;
        }
        if ($user->isLineManager()) {
            if ($idp->user_id === $user->id) return true;
            if (isset($idp->user) && $idp->user->line_manager_id === $user->id) return true;
        }
        // Employees cannot delete IDPs
        return false;
    }

    public function approve(User $user, Idp $idp)
    {
        if ($user->isHrAdmin()) return true;
        if ($user->isLineManager() && isset($idp->user) && $idp->user->line_manager_id === $user->id) return true;
        return false;
    }

    public function attain(User $user, Idp $idp)
    {
        // Attainment marking follows same rules as approval: HR, super, or line manager of the owner
        return $this->approve($user, $idp);
    }
}
