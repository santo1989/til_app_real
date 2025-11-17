<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Models\Objective;
use App\Models\User;
use App\Models\Department;
use App\Services\FinancialYearService;
use App\Http\Requests\ObjectiveSettingRequest;
use App\Models\FinancialYear;
use App\Models\Idp;
// SingleObjectiveRequest removed; ObjectiveSettingRequest handles single and bulk forms
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;

/**
 * @mixin User
 */
class ObjectiveController extends Controller
{
    public function __construct()
    {
        // Wire the Objective policy to this resource controller so policy methods
        // (view, create, update, delete) are automatically invoked for resource actions.
        $this->authorizeResource(\App\Models\Objective::class, 'objective');
    }
    // Resource CRUD for super admin/HR admin
    public function index()
    {
        $objectives = Objective::with(['user', 'department', 'creator'])->orderByDesc('id')->get();
        return view('appraisal.super_admin.objectives_index', compact('objectives'));
    }
    public function create()
    {
        $users = User::all();
        $departments = Department::all();

        // Get all financial years from database (use label)
        $years = FinancialYear::orderBy('start_date')->get()->pluck('label')->toArray();

        return view('appraisal.super_admin.objectives_create', compact('users', 'departments', 'years'));
    }
    public function store(ObjectiveSettingRequest $request)
    {
        $data = $request->validated();
        // If bulk payload provided, take the first objective for single-store usage
        if (isset($data['objectives']) && is_array($data['objectives']) && count($data['objectives']) > 0) {
            $data = $data['objectives'][0];
        }
        $data['created_by'] = auth()->id();
        // Enforce weightage sum <= 100 for user or department in a financial year
        $query = Objective::where('financial_year', $data['financial_year']);
        if ($data['type'] === 'individual') {
            $query->where('user_id', $data['user_id']);
        } elseif ($data['type'] === 'departmental' && $data['department_id']) {
            $query->where('department_id', $data['department_id']);
        }
        $totalWeight = $query->sum('weightage');
        if ($totalWeight + $data['weightage'] > 100) {
            return back()->withInput()->withErrors(['weightage' => 'Total weightage for this user/department in this financial year cannot exceed 100%.']);
        }
        Objective::create($data);
        return redirect()->route('objectives.index')->with('success', 'Objective created successfully.');
    }
    public function show(Objective $objective)
    {
        $objective->load(['user', 'department', 'creator']);
        return view('appraisal.super_admin.objectives_show', compact('objective'));
    }
    public function edit(Objective $objective)
    {
        $users = User::all();
        $departments = Department::all();

        // Get all financial years from database (use label)
        $years = FinancialYear::orderBy('start_date')->get()->pluck('label')->toArray();

        return view('appraisal.super_admin.objectives_edit', compact('objective', 'users', 'departments', 'years'));
    }
    public function update(ObjectiveSettingRequest $request, Objective $objective)
    {
        $data = $request->validated();
        if (isset($data['objectives']) && is_array($data['objectives']) && count($data['objectives']) > 0) {
            $data = $data['objectives'][0];
        }
        // Enforce weightage sum <= 100 for user or department in a financial year (exclude this objective)
        $query = Objective::where('financial_year', $data['financial_year'])->where('id', '!=', $objective->id);
        if ($data['type'] === 'individual') {
            $query->where('user_id', $data['user_id']);
        } elseif ($data['type'] === 'departmental' && $data['department_id']) {
            $query->where('department_id', $data['department_id']);
        }
        $totalWeight = $query->sum('weightage');
        if ($totalWeight + $data['weightage'] > 100) {
            return back()->withInput()->withErrors(['weightage' => 'Total weightage for this user/department in this financial year cannot exceed 100%.']);
        }
        $objective->update($data);
        return redirect()->route('objectives.show', $objective)->with('success', 'Objective updated successfully.');
    }
    public function destroy(Objective $objective)
    {
        $objective->delete();
        return redirect()->route('objectives.index')->with('success', 'Objective deleted.');
    }

    // Legacy and user-specific methods (adminIndex, myObjectives, submit, etc.)
    public function adminIndex()
    {
        $objectives = Objective::with(['user', 'department', 'creator'])->orderByDesc('id')->get();
        return view('appraisal.super_admin.objectives_index', compact('objectives'));
    }
    public function myObjectives()
    {
        /** @var User $user */
        $user = auth()->user();
        $activeFY = FinancialYear::getActiveName();
        $objectives = Objective::where('user_id', $user->id)
            ->where('financial_year', $activeFY)
            ->get();
        return view('appraisal.objectives.my', compact('objectives', 'activeFY'));
    }
    public function submit(ObjectiveSettingRequest $request)
    {
        $data = $request->validated();
        /** @var User $user */
        $user = auth()->user();
        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            $fyName = $fyService->label();
            $revisionAllowed = $fyService->isBeforeNinthMonth(now());
            $creationAllowed = $fyService->isWithinFirstMonth(now());
        } else {
            $fyName = FinancialYear::getActiveName();
            $revisionAllowed = true; // fallback: allow
            $creationAllowed = true; // fallback: allow
        }

        // If we couldn't determine an active financial year, stop early to avoid inserting NULL into DB
        if (empty($fyName)) {
            return back()->withErrors(['financial_year' => 'No active financial year found. Please create and activate a financial year before setting objectives.'])->withInput();
        }

        $existing = Objective::where('user_id', $user->id)
            ->where('financial_year', $fyName)
            ->exists();

        // If there are existing objectives, only allow revisions up to the 9th-month cutoff
        if ($existing && !$revisionAllowed) {
            return back()->withErrors(['objectives' => 'Objective revisions are locked after the 9th month of the financial year.'])->withInput();
        }

        // If no existing objectives, creation is only allowed within the first month of the FY
        if (!$existing && !$creationAllowed) {
            return back()->withErrors(['objectives' => 'Objective creation is only allowed during the first month of the financial year.'])->withInput();
        }

        Objective::where('user_id', $user->id)->where('financial_year', $fyName)->delete();
        foreach ($data['objectives'] as $obj) {
            Objective::create([
                'user_id' => auth()->id(),
                'type' => 'individual',
                'description' => $obj['description'],
                'weightage' => (int) $obj['weightage'],
                'target' => $obj['target'],
                'is_smart' => isset($obj['is_smart']) ? (bool) $obj['is_smart'] : false,
                'status' => 'set',
                'financial_year' => $fyName,
                'created_by' => auth()->id(),
            ]);
        }
        // Upsert IDP if payload provided
        if (!empty($data['idp']) && is_array($data['idp'])) {
            Idp::updateOrCreate(
                ['user_id' => auth()->id(), 'financial_year' => $fyName],
                [
                    'description' => $data['idp']['description'] ?? null,
                    'review_date' => isset($data['idp']['review_date']) ? $data['idp']['review_date'] : null,
                    'status' => $data['idp']['status'] ?? 'open',
                ]
            );
        }
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'objective_setting_submitted',
            'details' => "Objectives set for FY {$fyName}",
        ]);
        return redirect()->route('objectives.my')->with('success', 'Objectives saved successfully.');
    }
    public function teamObjectives()
    {
        /** @var User $user */
        $user = auth()->user();
        $activeFY = FinancialYear::getActiveName();
        $team = $user->reports()->with(['objectives' => function ($q) use ($activeFY) {
            $q->where('financial_year', $activeFY);
        }])->get();
        return view('appraisal.line_manager.team_objectives', compact('team', 'activeFY'));
    }
    public function showSetForUser($user_id)
    {
        $employee = User::findOrFail($user_id);
        if ($employee->line_manager_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        return view('appraisal.line_manager.set_objectives', compact('employee'));
    }
    public function setForUser(ObjectiveSettingRequest $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        if ($employee->line_manager_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            $fyName = $fyService->label();
            if (!$fyService->isBeforeNinthMonth(now())) {
                return back()->withErrors(['message' => 'Objective revisions are locked after the 9th month of the financial year.']);
            }
        } else {
            $fyName = FinancialYear::getActiveName();
        }
        $data = $request->validated();
        $employee->objectives()->where('financial_year', $fyName)->delete();
        foreach ($data['objectives'] as $obj) {
            Objective::create([
                'user_id' => $employee->id,
                'type' => 'individual',
                'description' => $obj['description'],
                'weightage' => (int) $obj['weightage'],
                'target' => $obj['target'],
                'is_smart' => isset($obj['is_smart']) ? (bool) $obj['is_smart'] : false,
                'status' => 'set',
                'financial_year' => $fyName,
                'created_by' => auth()->id(),
            ]);
        }
        // Upsert IDP if payload provided by line manager
        if (!empty($data['idp']) && is_array($data['idp'])) {
            Idp::updateOrCreate(
                ['user_id' => $employee->id, 'financial_year' => $fyName],
                [
                    'description' => $data['idp']['description'] ?? null,
                    'review_date' => isset($data['idp']['review_date']) ? $data['idp']['review_date'] : null,
                    'status' => $data['idp']['status'] ?? 'open',
                ]
            );
        }
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'objectives_set_for_employee',
            'details' => "Line manager set objectives for employee ID {$employee->id} for FY {$fyName}",
        ]);
        return redirect()->route('objectives.team')->with('success', "Objectives set for {$employee->name}.");
    }
    public function departmentObjectives(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $activeFY = FinancialYear::getActiveName();

        // Allow filtering by financial year via query string ?fy=YYYY-YY
        $financialYear = $request->get('fy', $activeFY);

        // Provide list of available FY labels for the selector
        $years = FinancialYear::orderBy('start_date')->get()->pluck('label')->toArray();

        $search = $request->get('q');

        // Paginate departmental objectives for large departments
        $objectives = Objective::with('user')
            ->where('type', 'departmental')
            ->where('department_id', $user->department_id)
            ->when($financialYear, function ($q) use ($financialYear) {
                $q->where('financial_year', $financialYear);
            })
            ->when($search, function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                  });
            })
            ->orderBy('id')
            ->paginate(15);

        return view('appraisal.dept_head.department_objectives', compact('objectives', 'activeFY', 'financialYear', 'years'));
    }

    /**
     * Export departmental objectives (filtered) as CSV
     */
    public function departmentExport(Request $request)
    {
        $user = auth()->user();
        $financialYear = $request->get('fy', FinancialYear::getActiveName());
        $search = $request->get('q');

        $query = Objective::with('user')
            ->where('type', 'departmental')
            ->where('department_id', $user->department_id)
            ->when($financialYear, fn($q) => $q->where('financial_year', $financialYear))
            ->when($search, function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                  });
            });

        $objectives = $query->orderBy('id')->get();

        $filename = 'department_objectives_' . ($financialYear ?? 'all') . '_' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($objectives) {
            $out = fopen('php://output', 'w');
            // Header
            fputcsv($out, ['ID', 'Description', 'Owner', 'Weightage', 'Target', 'Financial Year', 'Status']);
            foreach ($objectives as $o) {
                fputcsv($out, [
                    $o->id,
                    $o->description,
                    $o->user?->name ?? 'Department',
                    $o->weightage,
                    $o->target,
                    $o->financial_year,
                    $o->status,
                ]);
            }
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Bulk update selected departmental objectives. Accepts an array of IDs
     * and applies provided attributes (weightage, status, target) to each.
     */
    public function departmentBulkUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:objectives,id',
            'weightage' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|string',
            'target' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Ensure objectives belong to the user's department
        $updated = 0;
        $objs = Objective::whereIn('id', $data['ids'])->where('department_id', $user->department_id)->get();
        foreach ($objs as $o) {
            $changes = [];
            if (isset($data['weightage'])) $changes['weightage'] = (int)$data['weightage'];
            if (isset($data['status'])) $changes['status'] = $data['status'];
            if (isset($data['target'])) $changes['target'] = $data['target'];
            if (!empty($changes)) {
                $o->update($changes);
                $updated++;
            }
        }

        return back()->with('success', "Bulk update applied to {$updated} objectives.");
    }

    /**
     * Create a departmental objective inline for the current user's department.
     * This creates objectives for each active user in the department (same as teamObjectivesStore)
     * but scoped to the authenticated user's department and accessible to dept_head role.
     */
    public function departmentCreateInline(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'description' => 'required|string',
            'weightage' => 'required|integer|in:10,15,20,25,30',
            'target' => 'required|string',
            'is_smart' => 'nullable|boolean',
            'financial_year' => 'required|string',
        ]);

        $user = auth()->user();
        $departmentUsers = User::where('department_id', $user->department_id)->where('is_active', true)->get();

        foreach ($departmentUsers as $u) {
            Objective::create([
                'user_id' => $u->id,
                'department_id' => $user->department_id,
                'type' => 'departmental',
                'description' => $data['description'],
                'weightage' => $data['weightage'],
                'target' => $data['target'],
                'is_smart' => $data['is_smart'] ?? false,
                'status' => 'set',
                'financial_year' => $data['financial_year'],
                'created_by' => auth()->id(),
            ]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'departmental_objective_inline_created',
            'details' => "Inline departmental objective created for department {$user->department_id} FY {$data['financial_year']}",
        ]);

        return back()->with('success', 'Departmental objective created for all active users in the department.');
    }
    public function boardIndex()
    {
        $departments = Department::all();
        // provide current financial years list for the view if needed
        $years = FinancialYear::orderBy('start_date')->get()->pluck('label')->toArray();
        $activeFY = FinancialYear::getActiveName();
        return view('appraisal.board.set_departmental', compact('departments', 'years', 'activeFY'));
    }
    public function boardSet(Request $request)
    {
        $payload = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'objectives' => 'required|array|min:2|max:3',
            'objectives.*.description' => 'required|string',
            'objectives.*.weightage' => 'required|integer|in:10,15,20,25,30',
            'objectives.*.target' => 'required|string',
        ]);
        $sum = array_sum(array_column($payload['objectives'], 'weightage'));
        if ($sum !== 30) {
            return back()->withErrors(['objectives' => 'Departmental objectives must total 30%.'])->withInput();
        }

        $activeFY = FinancialYear::getActiveName();

        // Remove existing departmental objectives for this department & FY to avoid duplicates
        Objective::where('department_id', $payload['department_id'])
            ->where('type', 'departmental')
            ->where('financial_year', $activeFY)
            ->delete();

        // Create departmental objectives for each active user in the department
        $departmentUsers = User::where('department_id', $payload['department_id'])
            ->where('is_active', true)
            ->get();

        // The sum of departmental objectives being applied (e.g. 30)
        $deptObjectivesSum = array_sum(array_column($payload['objectives'], 'weightage'));
        $skipped = [];

        foreach ($departmentUsers as $user) {
            // Sum of existing individual objectives for the user in this FY
            $existingIndividual = Objective::where('user_id', $user->id)
                ->where('type', 'individual')
                ->where('financial_year', $activeFY)
                ->sum('weightage');

            // If adding departmental objectives would exceed 100%, skip this user
            if ($existingIndividual + $deptObjectivesSum > 100) {
                $skipped[] = $user->name;
                continue;
            }

            foreach ($payload['objectives'] as $o) {
                Objective::create([
                    'user_id' => $user->id,
                    'department_id' => $payload['department_id'],
                    'type' => 'departmental',
                    'description' => $o['description'],
                    'weightage' => (int) $o['weightage'],
                    'target' => $o['target'],
                    'is_smart' => true,
                    'status' => 'set',
                    'financial_year' => $activeFY,
                    'created_by' => auth()->id(),
                ]);
            }
        }
        $details = "Departmental objectives set for FY {$activeFY} for department {$payload['department_id']}";
        if (!empty($skipped)) {
            $details .= '. Skipped users: ' . implode(', ', $skipped);
        }
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'departmental_objectives_set',
            'details' => $details,
        ]);

        $msg = 'Departmental objectives saved.';
        if (!empty($skipped)) {
            $msg .= ' Some users were skipped because existing individual objectives would cause their total weight to exceed 100%: ' . implode(', ', $skipped) . '.';
        }

        return redirect()->back()->with('success', $msg);
    }

    // Team Objectives: per-user list and CRUD for line managers; read-only for dept_head/board
    public function userObjectives(Request $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        // Line Managers can only see their direct reports; others (dept_head/board) reach here via their own middleware/routes
        if ($request->routeIs('users.objectives.*') && $employee->line_manager_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        $financialYear = $request->get('fy', FinancialYear::getActiveName());
        $objectives = Objective::where('user_id', $employee->id)
            ->where('type', 'individual')
            ->where('financial_year', $financialYear)
            ->orderBy('id')
            ->get();
        $canManage = ($employee->line_manager_id === auth()->id());

        // Get all financial years from database (use label)
        $years = FinancialYear::orderBy('start_date')->get()->pluck('label')->toArray();

        return view('appraisal.objectives.user_index', compact('employee', 'objectives', 'financialYear', 'years', 'canManage'));
    }

    public function createForUser(Request $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        if ($employee->line_manager_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isBeforeNinthMonth(now())) {
                return back()->withErrors(['message' => 'Objective revisions are locked after the 9th month of the financial year.']);
            }
        }

        // Get all financial years from database (use label)
        $years = FinancialYear::orderBy('start_date')->get()->pluck('label')->toArray();
        $financialYear = $request->get('fy', FinancialYear::getActiveName());

        return view('appraisal.objectives.user_form', [
            'employee' => $employee,
            'years' => $years,
            'financialYear' => $financialYear,
            'objective' => null,
        ]);
    }

    public function storeForUser(ObjectiveSettingRequest $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        if ($employee->line_manager_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isBeforeNinthMonth(now())) {
                return back()->withErrors(['message' => 'Objective revisions are locked after the 9th month of the financial year.']);
            }
        }

        $data = $request->validated();
        if (isset($data['objectives']) && is_array($data['objectives']) && count($data['objectives']) > 0) {
            $data = $data['objectives'][0];
        }
        // Enforce weightage sum <= 100 for the user in a financial year
        $existingWeight = Objective::where('user_id', $employee->id)
            ->where('type', 'individual')
            ->where('financial_year', $data['financial_year'])
            ->sum('weightage');
        if ($existingWeight + (int) $data['weightage'] > 100) {
            return back()->withInput()->withErrors(['weightage' => 'Adding this objective exceeds the total 100% weightage for this financial year.']);
        }
        Objective::create([
            'user_id' => $employee->id,
            'type' => 'individual',
            'description' => $data['description'],
            'weightage' => (int) $data['weightage'],
            'target' => $data['target'],
            'is_smart' => isset($data['is_smart']) ? (bool) $data['is_smart'] : false,
            'status' => 'set',
            'financial_year' => $data['financial_year'],
            'created_by' => auth()->id(),
        ]);
        return redirect()->route('users.objectives.index', ['user_id' => $employee->id, 'fy' => $data['financial_year']])
            ->with('success', 'Objective added.');
    }

    public function editForUser(Request $request, $user_id, Objective $objective)
    {
        $employee = User::findOrFail($user_id);
        if ($employee->line_manager_id !== auth()->id() || $objective->user_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        // Get all financial years from database (use label)
        $years = FinancialYear::orderBy('start_date')->get()->pluck('label')->toArray();
        $activeFY = FinancialYear::getActiveName();
        $financialYear = $request->get('fy', $objective->financial_year ?? $activeFY);

        return view('appraisal.objectives.user_form', compact('employee', 'objective', 'years', 'financialYear'));
    }

    public function updateForUser(ObjectiveSettingRequest $request, $user_id, Objective $objective)
    {
        $employee = User::findOrFail($user_id);
        if ($employee->line_manager_id !== auth()->id() || $objective->user_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isBeforeNinthMonth(now())) {
                return back()->withErrors(['message' => 'Objective revisions are locked after the 9th month of the financial year.']);
            }
        }

        $data = $request->validated();
        if (isset($data['objectives']) && is_array($data['objectives']) && count($data['objectives']) > 0) {
            $data = $data['objectives'][0];
        }
        // Enforce weightage sum <= 100 for the user in a financial year (exclude this objective)
        $existingWeight = Objective::where('user_id', $employee->id)
            ->where('type', 'individual')
            ->where('financial_year', $data['financial_year'])
            ->where('id', '!=', $objective->id)
            ->sum('weightage');
        if ($existingWeight + (int) $data['weightage'] > 100) {
            return back()->withInput()->withErrors(['weightage' => 'Updating this objective exceeds the total 100% weightage for this financial year.']);
        }
        $objective->update([
            'description' => $data['description'],
            'weightage' => (int) $data['weightage'],
            'target' => $data['target'],
            'is_smart' => isset($data['is_smart']) ? (bool) $data['is_smart'] : false,
            'financial_year' => $data['financial_year'],
        ]);
        return redirect()->route('users.objectives.index', ['user_id' => $employee->id, 'fy' => $data['financial_year']])
            ->with('success', 'Objective updated.');
    }

    public function destroyForUser(Request $request, $user_id, Objective $objective)
    {
        $employee = User::findOrFail($user_id);
        if ($employee->line_manager_id !== auth()->id() || $objective->user_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $activeFY = FinancialYear::getActive();
        if ($activeFY && !$activeFY->isRevisionAllowed()) {
            return back()->withErrors(['message' => 'Objective deletions are locked after the 9th month of the financial year.']);
        }

        $fy = $objective->financial_year;
        $objective->delete();
        return redirect()->route('users.objectives.index', ['user_id' => $employee->id, 'fy' => $fy])
            ->with('success', 'Objective deleted.');
    }

    private function isRevisionAllowed(string $financialYear): bool
    {
        // Try to resolve by label first (preferred). If not found, fall back to an in-memory
        // collection lookup by legacy 'name' to avoid SQL errors if that column was dropped.
        $fy = FinancialYear::where('label', $financialYear)->first();
        if ($fy) {
            return $fy->isRevisionAllowed();
        }

        $all = FinancialYear::all();
        $fy = $all->firstWhere('name', $financialYear);
        if ($fy) {
            return $fy->isRevisionAllowed();
        }
        // Fallback to old logic if FY not found
        [$startYear] = explode('-', $financialYear);
        $start = \Carbon\Carbon::parse($startYear . '-07-01');
        $cutoff = (clone $start)->addMonths(9)->endOfDay();
        return now()->lessThanOrEqualTo($cutoff);
    }

    // Team Objectives CRUD (type='team', department-wide)
    public function teamObjectivesIndex()
    {
        /** @var User $user */
        $user = auth()->user();
        // Line managers see their department's team objectives
        $teamObjectives = Objective::with(['department', 'creator'])
            ->where('type', 'departmental')
            ->where('department_id', $user->department_id)
            ->orderByDesc('id')
            ->get();
        return view('appraisal.line_manager.team_objectives_manage', compact('teamObjectives'));
    }

    public function teamObjectivesCreate()
    {
        $departments = Department::all();
        $years = [];
        $start = 2025;
        for ($i = 0; $i < 11; $i++) {
            $fy = ($start + $i) . '-' . substr($start + $i + 1, -2);
            $years[] = $fy;
        }
        return view('appraisal.line_manager.team_objectives_form', [
            'objective' => null,
            'departments' => $departments,
            'years' => $years,
        ]);
    }

    public function teamObjectivesStore(Request $request)
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'description' => 'required|string',
            'weightage' => 'required|integer|in:10,15,20,25,30',
            'target' => 'required|string',
            'is_smart' => 'nullable|boolean',
            'financial_year' => 'required|string',
        ]);

        // Check total weightage for team objectives in this department/FY <= 30%
        $totalWeight = Objective::where('type', 'team')
            ->where('department_id', $data['department_id'])
            ->where('financial_year', $data['financial_year'])
            ->sum('weightage');

        if ($totalWeight + $data['weightage'] > 30) {
            return back()->withInput()->withErrors(['weightage' => 'Total team objectives weightage cannot exceed 30% for this department in this financial year.']);
        }

        // Get all users in the selected department
        $departmentUsers = User::where('department_id', $data['department_id'])
            ->where('is_active', true)
            ->get();

        // Create team objective for each user in the department
        foreach ($departmentUsers as $user) {
            Objective::create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'],
                'type' => 'departmental',
                'description' => $data['description'],
                'weightage' => $data['weightage'],
                'target' => $data['target'],
                'is_smart' => $data['is_smart'] ?? false,
                'status' => 'set',
                'financial_year' => $data['financial_year'],
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('team.objectives.index')->with('success', 'Team objective created successfully for all department members.');
    }

    public function teamObjectivesShow(Objective $team_objective)
    {
        $team_objective->load(['department', 'creator']);
        return view('appraisal.line_manager.team_objectives_show', ['objective' => $team_objective]);
    }

    public function teamObjectivesEdit(Objective $team_objective)
    {
        $departments = Department::all();
        $years = [];
        $start = 2025;
        for ($i = 0; $i < 11; $i++) {
            $fy = ($start + $i) . '-' . substr($start + $i + 1, -2);
            $years[] = $fy;
        }
        return view('appraisal.line_manager.team_objectives_form', [
            'objective' => $team_objective,
            'departments' => $departments,
            'years' => $years,
        ]);
    }

    public function teamObjectivesUpdate(Request $request, Objective $team_objective)
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'description' => 'required|string',
            'weightage' => 'required|integer|in:10,15,20,25,30',
            'target' => 'required|string',
            'is_smart' => 'nullable|boolean',
            'financial_year' => 'required|string',
        ]);

        // Check total weightage for team objectives in this department/FY <= 30% (exclude current)
        $totalWeight = Objective::where('type', 'team')
            ->where('department_id', $data['department_id'])
            ->where('financial_year', $data['financial_year'])
            ->where('id', '!=', $team_objective->id)
            ->sum('weightage');

        if ($totalWeight + $data['weightage'] > 30) {
            return back()->withInput()->withErrors(['weightage' => 'Total team objectives weightage cannot exceed 30% for this department in this financial year.']);
        }

        $team_objective->update([
            'department_id' => $data['department_id'],
            'description' => $data['description'],
            'weightage' => $data['weightage'],
            'target' => $data['target'],
            'is_smart' => $data['is_smart'] ?? false,
            'financial_year' => $data['financial_year'],
        ]);

        return redirect()->route('team.objectives.show', $team_objective)->with('success', 'Team objective updated successfully.');
    }

    public function teamObjectivesDestroy(Objective $team_objective)
    {
        $team_objective->delete();
        return redirect()->route('team.objectives.index')->with('success', 'Team objective deleted successfully.');
    }

    /**
     * Generate PDF for employee's objectives
     */
    public function generatePDF($user_id, Request $request)
    {
        $employee = User::with(['department', 'lineManager'])->findOrFail($user_id);
        $financialYear = $request->get('fy', FinancialYear::getActiveName());

        $objectives = Objective::where('user_id', $user_id)
            ->where('type', 'individual')
            ->where('financial_year', $financialYear)
            ->orderBy('id')
            ->get();

        // Log PDF generation
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'generate_objectives_pdf',
            'table_name' => 'objectives',
            'record_id' => $user_id,
            'details' => "Generated objectives PDF for {$employee->name} - FY: {$financialYear}",
        ]);

        $pdf = Pdf::loadView('appraisal.pdf.objectives_form', compact('employee', 'objectives', 'financialYear'));

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        $fileName = "Objectives_{$employee->name}_{$financialYear}.pdf";
        $fileName = str_replace(' ', '_', $fileName);

        return $pdf->download($fileName);
    }
}
