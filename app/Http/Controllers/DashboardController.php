<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Objective;
use App\Models\Appraisal;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        switch ($user->role ?? 'employee') {
            case 'super_admin':
                // Super admin gets comprehensive system overview
                $stats = [
                    'total_users' => User::count(),
                    'active_users' => User::where('is_active', true)->count(),
                    'total_departments' => Department::count(),
                    'total_objectives' => Objective::count(),
                    'pending_objectives' => Objective::where('status', 'draft')->count(),
                    'approved_objectives' => Objective::where('status', 'set')->count(),
                    'total_appraisals' => Appraisal::count(),
                    'pending_appraisals' => Appraisal::where('status', 'pending')->count(),
                    'completed_appraisals' => Appraisal::where('status', 'completed')->count(),
                ];

                $recentUsers = User::latest()->take(5)->get();
                $recentObjectives = Objective::with('user')->latest()->take(5)->get();
                $recentAppraisals = Appraisal::with('user')->latest()->take(5)->get();
                $departments = Department::withCount('users')->get();

                return view('appraisal.super_admin.dashboard', compact('stats', 'recentUsers', 'recentObjectives', 'recentAppraisals', 'departments'));

            case 'hr_admin':
                $stats = [
                    'total_users' => User::count(),
                    'active_users' => User::where('is_active', true)->count(),
                    'total_departments' => Department::count(),
                ];
                $departments = Department::all();
                return view('appraisal.hr_admin.dashboard', compact('stats', 'departments'));

            case 'line_manager':
                $teamSize = User::where('line_manager_id', $user->id)->count();
                $pendingMidterms = Appraisal::where('type', 'midterm')
                    ->where('status', 'pending')
                    ->whereHas('user', function ($q) use ($user) {
                        $q->where('line_manager_id', $user->id);
                    })
                    ->count();
                $pendingYearend = Appraisal::where('type', 'yearend')
                    ->where('status', 'pending')
                    ->whereHas('user', function ($q) use ($user) {
                        $q->where('line_manager_id', $user->id);
                    })
                    ->count();
                $stats = [
                    'team_size' => $teamSize,
                    'pending_midterms' => $pendingMidterms,
                    'pending_yearend' => $pendingYearend,
                ];
                return view('appraisal.line_manager.dashboard', compact('stats'));

            case 'dept_head':
                $deptId = $user->department_id;
                $departmentObjectives = Objective::where('department_id', $deptId)->with('user')->get();
                return view('appraisal.dept_head.dashboard', compact('departmentObjectives'));

            case 'board':
                $reports = Appraisal::latest()->limit(20)->get();
                return view('appraisal.board.dashboard', compact('reports'));

            default:
                $myObjectivesCount = Objective::where('user_id', $user->id)->count();
                $midtermDue = Appraisal::where('user_id', $user->id)->where('type', 'midterm')->where('status', 'pending')->count();
                $yearendDue = Appraisal::where('user_id', $user->id)->where('type', 'yearend')->where('status', 'pending')->count();
                $myIdps = \App\Models\Idp::where('user_id', $user->id)->count();
                $stats = [
                    'my_objectives' => $myObjectivesCount,
                    'midterm_due' => $midtermDue,
                    'yearend_due' => $yearendDue,
                    'my_idps' => $myIdps,
                ];
                return view('appraisal.employee.dashboard', compact('stats'));
        }
    }
}
