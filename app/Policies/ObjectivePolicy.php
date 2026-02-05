<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Objective;
use App\Models\FinancialYear;
use App\Services\FinancialYearService;
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

class ObjectivePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Super admin has full access
        if ($user->isSuperAdmin()) return true;

        // HR admin and leadership can view lists
        return in_array($user->role, ['hr_admin', 'line_manager', 'dept_head', 'board']);
    }

    public function view(User $user, Objective $objective): bool
    {
        // Super admin has full access
        if ($user->isSuperAdmin()) return true;

        if ($user->isHrAdmin()) return true;
        if ($user->isLineManager()) {
            // Managers can view their own objectives and their direct reports'
            return $objective->user_id === $user->id
                || ($objective->user && $objective->user->line_manager_id === $user->id);
        }
        if ($user->isDeptHead() || $user->isBoardMember()) {
            // Heads/Board can view but not necessarily edit; keep simple: allow
            return true;
        }
        // employees can view their own objectives
        // Additionally allow employees to view departmental (team) objectives
        if ($objective->user_id === $user->id) return true;
        if ($objective->department_id && $user->department_id && $objective->department_id === $user->department_id) return true;
        return false;
    }

    public function create(User $user): bool
    {
        // Super admin has full access
        if ($user->isSuperAdmin()) return true;

        // employees and managers and HR can create objectives
        return in_array($user->role, ['employee', 'line_manager', 'hr_admin', 'board']);
    }

    public function update(User $user, Objective $objective): bool
    {
        // Super admin has full access
        if ($user->isSuperAdmin()) return true;

        // If objective already set/approved, only HR or Super Admin can edit team/departmental objectives.
        if (isset($objective->status) && $objective->status === 'set') {
            // Allow users with HR or Super access to edit approved items
            if ($user->isHrAdmin() || $user->isSuperAdmin()) return true;
            // Employees may be allowed to edit their own approved objectives within the allowed window (below)
        }

        // Allow HR admins to always edit
        if ($user->isHrAdmin()) return true;

        // Determine financial year windows
        $activeFy = FinancialYear::getActive();
        $fyService = new FinancialYearService($activeFy);
        $now = Carbon::now();
        $beforeMidterm = $activeFy ? $now->lte($fyService->midtermDate()) : true;
        $beforeNinth = $activeFy ? $now->lte($fyService->ninthMonthCutoff()) : true;


        // Line managers can update their own objectives and their team's objectives
        if ($user->isLineManager()) {
            // If manager is editing their own objective
            if ($objective->user_id === $user->id) {
                return $beforeNinth;
            }
            // If manager is editing a team member's objective
            if ($objective->user && $objective->user->line_manager_id === $user->id) {
                // For team/departmental objectives, require HR approval before managers may edit
                if (in_array($objective->type, ['departmental', 'team'])) {
                    $approver = $objective->approver ?? null;
                    if (! $approver || ($approver && $approver->role !== 'hr_admin')) {
                        return false;
                    }
                }
                return $beforeNinth;
            }
            return false;
        }

        // Employee may update their own objective but only after line-manager approval and within allowed revision window
        if ($user->role === 'employee') {
            if ($objective->user_id !== $user->id) return false;
            // Must have been approved by a line manager before employee edits
            if (empty($objective->approved_by)) return false;
            return $beforeNinth;
        }

        return false;
    }

    public function delete(User $user, Objective $objective): bool
    {
        // Super admin has full access
        if ($user->isSuperAdmin()) return true;

        return $user->isHrAdmin();
    }

    /**
     * Determine whether the user can approve the given objective.
     */
    public function approve(User $user, Objective $objective): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isHrAdmin()) return true;
        if ($user->isLineManager()) {
            return $objective->user && $objective->user->line_manager_id === $user->id;
        }
        return false;
    }

    /**
     * Determine whether the user can reject the given objective.
     */
    public function reject(User $user, Objective $objective): bool
    {
        return $this->approve($user, $objective);
    }

    /**
     * Determine whether the user can enter or edit the % achieved fields for an objective.
     */
    public function enterAchieved(User $user, Objective $objective): bool
    {
        // Super and HR can always enter/edit achieved values
        if ($user->isSuperAdmin() || $user->isHrAdmin()) return true;

        // Line managers can enter achieved for their direct reports
        if ($user->isLineManager()) {
            if ($objective->user && $objective->user->line_manager_id === $user->id) {
                // If an achieved value already exists and was entered by a line manager,
                // only HR or SuperAdmin may change it per business rules.
                if (!empty($objective->target_achieved_entered_by)) {
                    $enterer = \App\Models\User::find($objective->target_achieved_entered_by);
                    if ($enterer && $enterer->role === 'line_manager') {
                        return false; // only HR/SuperAdmin can edit after manager entry
                    }
                }
                return true;
            }
        }

        return false;
    }
}
