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
use Illuminate\Support\Facades\Gate;
use App\Models\Idp;
// SingleObjectiveRequest removed; ObjectiveSettingRequest handles single and bulk forms
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use App\Models\MidtermProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * @mixin User
 */
class ObjectiveController extends Controller
{
    public function __construct()
    {
        // Wire the Objective policy to this resource controller so policy methods
        // (view, create, update, delete) are automatically invoked for resource actions.
        $this->authorizeResource(Objective::class, 'objective');
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
        // Prevent creation of departmental/team objectives outside the allowed creation window
        if (in_array($data['type'] ?? '', ['departmental', 'team'])) {
            $fyLabel = $data['financial_year'] ?? null;
            if ($fyLabel && !$this->isCreationAllowed($fyLabel)) {
                return back()->withErrors(['message' => 'Cannot create departmental/team objectives at this time (outside allowed creation window).'])->withInput();
            }
        }
        $objective = Objective::create($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'objective_created',
            'table_name' => 'objectives',
            'record_id' => $objective->id,
            'details' => "Objective created: {$objective->description} (ID {$objective->id})",
        ]);
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
        // Prevent editing departmental/team objectives outside the allowed creation/revision window
        $type = $data['type'] ?? $objective->type;
        $fyLabel = $data['financial_year'] ?? $objective->financial_year;
        if (in_array($type, ['departmental', 'team']) && !$this->isCreationAllowed($fyLabel)) {
            return back()->withErrors(['message' => 'Cannot modify departmental/team objectives at this time (outside allowed window).'])->withInput();
        }
        $objective->update($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'objective_updated',
            'table_name' => 'objectives',
            'record_id' => $objective->id,
            'details' => "Objective updated: {$objective->description} (ID {$objective->id})",
        ]);
        return redirect()->route('objectives.show', $objective)->with('success', 'Objective updated successfully.');
    }
    public function destroy(Objective $objective)
    {
        // Prevent deletion of departmental/team objectives after revision cutoff
        if (in_array($objective->type, ['departmental', 'team']) && !$this->isRevisionAllowed($objective->financial_year)) {
            return back()->withErrors(['message' => 'Cannot delete departmental/team objectives after the 9th month of the financial year.']);
        }

        $objId = $objective->id;
        $desc = $objective->description;
        $objective->delete();
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'objective_deleted',
            'table_name' => 'objectives',
            'record_id' => $objId,
            'details' => "Objective deleted: {$desc} (ID {$objId})",
        ]);
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
        /** @var User $user */
        $user = auth()->user();
        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            $fyName = $fyService->label();
            $revisionAllowed = $fyService->isBeforeNinthMonth(now());

            // New rule: creation allowed until the 6-month review date when within first 6 months;
            // if the 6th month has passed, allow creation up to the 9-month revision cutoff.
            // Compute based on the financial year's start_date.
            try {
                $start = \Carbon\Carbon::parse($activeModel->start_date)->startOfDay();
                $sixMonthReview = (clone $start)->addMonths(6)->endOfDay();
                $nineMonthCutoff = (clone $start)->addMonths(9)->endOfDay();

                if (now()->greaterThan($sixMonthReview)) {
                    // we're past the 6-month review: allow up to 9-month cutoff
                    $creationAllowed = now()->lessThanOrEqualTo($nineMonthCutoff);
                } else {
                    // within first 6 months: allow up to 6-month review date
                    $creationAllowed = now()->lessThanOrEqualTo($sixMonthReview);
                }
            } catch (\Throwable $e) {
                // Fallback conservative behaviour: disallow creation if dates can't be parsed
                $creationAllowed = false;
            }
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

        // Employee submissions require approval from the line manager or HR admin.
        // Mark newly created objectives as 'pending' so approvers can review them.
        Objective::where('user_id', $user->id)->where('financial_year', $fyName)->delete();
        foreach ($data['objectives'] as $obj) {
            Objective::create([
                'user_id' => auth()->id(),
                'type' => 'individual',
                'description' => $obj['description'],
                'weightage' => (int) $obj['weightage'],
                'target' => $obj['target'],
                'is_smart' => isset($obj['is_smart']) ? (bool) $obj['is_smart'] : false,
                'status' => 'pending',
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

        // Persist any signature images submitted from the employee objective form.
        // Expected inputs (base64 data URLs): sign_employee_obj, sign_manager_obj, sign_hr_obj
        try {
            $this->persistInlineSignatures(auth()->id(), 'objectives', [
                'sign_employee_obj' => 'employee_signature_obj',
                'sign_manager_obj' => 'manager_signature_obj',
                'sign_hr_obj' => 'hr_signature_obj',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to persist objectives signatures', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);
        }
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'objective_setting_submitted_pending',
            'details' => "Objectives submitted (pending approval) for FY {$fyName}",
        ]);
        return redirect()->route('objectives.my')->with('success', 'Objectives saved successfully.');
    }

    /**
     * Persist base64 signature inputs to disk.
     * @param int $userId
     * @param string $context subfolder/context label like 'objectives','midterm','yearend'
     * @param array $mapping array of inputName => column/key used in log
     * @return void
     */
    private function persistInlineSignatures(int $userId, string $context, array $mapping): void
    {
        foreach ($mapping as $inputName => $logKey) {
            $val = request()->input($inputName);
            if (empty($val)) continue;
            // Expect data:image/png;base64,...
            if (!is_string($val) || strpos($val, 'base64,') === false) continue;
            $data = substr($val, strpos($val, ',') + 1);
            $bin = base64_decode($data);
            if ($bin === false) continue;
            // small size enforcement
            $maxBytes = 500 * 1024; // 500KB
            if (strlen($bin) > $maxBytes) {
                // try to downscale using GD if available
                if (function_exists('imagecreatefromstring')) {
                    $src = @imagecreatefromstring($bin);
                    if ($src !== false) {
                        $w = imagesx($src);
                        $h = imagesy($src);
                        $scale = sqrt($maxBytes / strlen($bin));
                        $nw = max(100, (int)($w * $scale));
                        $nh = max(40, (int)($h * $scale));
                        $dst = imagecreatetruecolor($nw, $nh);
                        imagealphablending($dst, false);
                        imagesavealpha($dst, true);
                        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
                        imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
                        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                        ob_start();
                        imagepng($dst);
                        $bin = ob_get_clean();
                        imagedestroy($dst);
                        imagedestroy($src);
                    }
                }
            }
            $path = 'signatures/' . $userId . '/' . $context . '/' . now()->format('Ymd') . '/' . uniqid() . '.png';
            try {
                $dir = dirname($path);
                if ($dir && $dir !== '.') Storage::disk('public')->makeDirectory($dir);
                Storage::disk('public')->put($path, $bin);
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'signature_saved',
                    'table_name' => null,
                    'record_id' => null,
                    'details' => "Saved signature for {$context} ({$inputName}) to {$path}",
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed storing inline signature', ['path' => $path, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Approve a pending objective. Allowed for the user's line manager, HR admin, or super admin.
     */
    public function approve(Request $request, Objective $objective)
    {
        // Authorization via policy
        $this->authorize('approve', $objective);

        // Ensure approvals happen within the allowed creation/revision window (6/9-month logic).
        // If the financial year record cannot be resolved (legacy labels or missing FY row),
        // allow the approver to proceed (fallback permissive behavior) so managers are not
        // blocked by FY label parsing issues.
        $fyModel = FinancialYear::where('label', $objective->financial_year)->first();
        if ($fyModel) {
            if (!$this->isCreationAllowed($objective->financial_year)) {
                return back()->withErrors(['message' => 'Approvals are locked outside the allowed review window for this financial year.']);
            }
        }
        $user = auth()->user();

        // Before approving, ensure we will not exceed the maximum approved objectives per user
        $currentApproved = Objective::where('user_id', $objective->user_id)
            ->where('financial_year', $objective->financial_year)
            ->where('status', 'set')
            ->count();

        if ($currentApproved >= 6) {
            return back()->withErrors(['message' => 'Cannot approve this objective: user already has maximum of 6 approved objectives.']);
        }

        $objective->status = 'set';
        $objective->rejection_reason = null;
        $objective->approved_by = $user->id;
        $objective->approved_at = now();
        $objective->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'objective_approved',
            'table_name' => 'objectives',
            'record_id' => $objective->id,
            'details' => "Objective ID {$objective->id} approved by user {$user->id}",
        ]);

        // Send notification to objective owner
        try {
            $objective->user->notify(new \App\Notifications\ObjectiveStatusChanged($objective, 'approved', $user));
        } catch (\Throwable $e) {
            // Do not fail approval if notification fails. Log if desired.
        }

        // Post-approval: check if approved objectives count meets minimum requirement
        $newCount = Objective::where('user_id', $objective->user_id)
            ->where('financial_year', $objective->financial_year)
            ->where('status', 'set')
            ->count();

        $msg = 'Objective approved.';
        if ($newCount < 3) {
            $msg .= ' Note: user has fewer than 3 approved objectives; please approve additional objectives to reach the minimum of 3.';
        }

        return back()->with('success', $msg);
    }

    /**
     * Reject a pending objective. Allowed for the user's line manager, HR admin, or super admin.
     */
    public function reject(Request $request, Objective $objective)
    {
        $this->authorize('reject', $objective);

        // Allow rejection when FY cannot be resolved (fallback) but otherwise enforce window
        $fyModel = FinancialYear::where('label', $objective->financial_year)->first();
        if ($fyModel) {
            if (!$this->isCreationAllowed($objective->financial_year)) {
                return back()->withErrors(['message' => 'Actions are locked outside the allowed review window for this financial year.']);
            }
        }
        $user = auth()->user();

        $reason = $request->input('reason');
        $objective->status = 'rejected';
        $objective->rejection_reason = $reason;
        $objective->approved_by = $user->id;
        $objective->approved_at = now();
        $objective->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'objective_rejected',
            'table_name' => 'objectives',
            'record_id' => $objective->id,
            'details' => "Objective ID {$objective->id} rejected by user {$user->id}. Reason: " . ($reason ?? 'N/A'),
        ]);

        try {
            $objective->user->notify(new \App\Notifications\ObjectiveStatusChanged($objective, 'rejected', $user, $reason));
        } catch (\Throwable $e) {
            // ignore notification failures
        }

        return back()->with('success', 'Objective rejected.');
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

    /**
     * List pending objectives for the authenticated line manager's direct reports.
     */
    public function approvals(Request $request)
    {
        $user = auth()->user();
        // Filters
        $q = $request->get('q');
        $fy = $request->get('fy');
        $from = $request->get('from');
        $to = $request->get('to');

        $query = Objective::with('user')
            ->where('type', 'individual')
            ->where('status', 'pending')
            ->whereHas('user', function ($uq) use ($user) {
                $uq->where('line_manager_id', $user->id);
            });

        if (!empty($q)) {
            $query->whereHas('user', function ($uq) use ($q) {
                $uq->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
            })->orWhere('description', 'like', "%{$q}%");
        }
        if (!empty($fy)) {
            $query->where('financial_year', $fy);
        }
        if (!empty($from)) {
            $query->whereDate('created_at', '>=', $from);
        }
        if (!empty($to)) {
            $query->whereDate('created_at', '<=', $to);
        }

        $pending = $query->orderBy('user_id')->paginate(15)->appends($request->query());

        // Per-employee counts map for quick display
        $userIds = collect($pending->items())->pluck('user_id')->unique()->filter()->values()->toArray();
        $counts = [];
        if (!empty($userIds)) {
            $rows = Objective::select('user_id', 'status', DB::raw('count(*) as cnt'))
                ->whereIn('user_id', $userIds)
                ->whereIn('status', ['pending', 'set', 'rejected'])
                ->groupBy('user_id', 'status')
                ->get();
            foreach ($rows as $r) {
                $counts[$r->user_id][$r->status] = $r->cnt;
            }
        }

        // Midterm progress map (if model exists)
        $midterm = [];
        if (class_exists('\App\\Models\\MidtermProgress')) {
            $mpClass = '\\App\\Models\\MidtermProgress';
            $mpRows = $mpClass::whereIn('user_id', $userIds)
                ->select('user_id', DB::raw('max(recorded_at) as recorded_at'))
                ->groupBy('user_id')
                ->get();
            $latest = [];
            foreach ($mpRows as $r) $latest[$r->user_id] = $r->recorded_at;
            if (!empty($latest)) {
                $mpEntries = $mpClass::whereIn('user_id', array_keys($latest))
                    ->whereIn('recorded_at', array_values($latest))
                    ->get();
                foreach ($mpEntries as $e) {
                    $midterm[$e->user_id] = $e->progress_percent;
                }
            }
        }

        return view('appraisal.line_manager.approvals', compact('pending', 'counts', 'midterm'));
    }

    /**
     * Bulk approve selected objectives.
     */
    public function bulkApprove(Request $request)
    {
        $user = auth()->user();
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return back()->withErrors(['ids' => 'No objectives selected for approval.']);
        }
        $objs = Objective::whereIn('id', $ids)->get();
        DB::beginTransaction();
        try {
            $updated = 0;
            foreach ($objs as $o) {
                if (!Gate::allows('approve', $o)) continue;
                // ensure not exceeding 6 approved per user
                $currentApproved = Objective::where('user_id', $o->user_id)->where('financial_year', $o->financial_year)->where('status', 'set')->count();
                if ($currentApproved >= 6) continue;
                $o->status = 'set';
                $o->rejection_reason = null;
                $o->approved_by = $user->id;
                $o->approved_at = now();
                $o->save();
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'objective_bulk_approved',
                    'table_name' => 'objectives',
                    'record_id' => $o->id,
                    'details' => "Objective ID {$o->id} bulk-approved by user {$user->id}",
                ]);
                $updated++;
                try {
                    $o->user->notify(new \App\Notifications\ObjectiveStatusChanged($o, 'approved', $user));
                } catch (\Throwable $e) {
                }
            }
            DB::commit();
            return back()->with('success', "Bulk approve completed. Approved {$updated} objectives.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Bulk approve failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk reject selected objectives.
     */
    public function bulkReject(Request $request)
    {
        $user = auth()->user();
        $ids = $request->input('ids', []);
        $reason = $request->input('reason');
        // Accept either an array of ids or a JSON-encoded string (from the modal)
        if (is_string($ids)) {
            $decoded = json_decode($ids, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $ids = $decoded;
            } else {
                // attempt to parse comma-separated values
                $ids = array_filter(array_map('trim', explode(',', $ids)), fn($v) => $v !== '');
            }
        }
        // Normalize to integers
        if (is_array($ids)) {
            $ids = array_map(fn($v) => (int)$v, $ids);
        }
        if (empty($ids) || !is_array($ids)) {
            return back()->withErrors(['ids' => 'No objectives selected for rejection.']);
        }
        DB::beginTransaction();
        try {
            $objs = Objective::whereIn('id', $ids)->get();
            $updated = 0;
            foreach ($objs as $o) {
                if (!Gate::allows('reject', $o)) continue;
                $o->status = 'rejected';
                $o->rejection_reason = $reason;
                $o->approved_by = $user->id;
                $o->approved_at = now();
                $o->save();
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'objective_bulk_rejected',
                    'table_name' => 'objectives',
                    'record_id' => $o->id,
                    'details' => "Objective ID {$o->id} bulk-rejected by user {$user->id}. Reason: " . ($reason ?? 'N/A'),
                ]);
                try {
                    $o->user->notify(new \App\Notifications\ObjectiveStatusChanged($o, 'rejected', $user, $reason));
                } catch (\Throwable $e) {
                }
                $updated++;
            }
            DB::commit();
            return back()->with('success', "Bulk reject completed. Rejected {$updated} objectives.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Bulk reject failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a midterm progress entry (created by line manager).
     */
    public function storeMidterm(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'objective_id' => 'nullable|integer|exists:objectives,id',
            'financial_year' => 'nullable|string',
            'progress_percent' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Authorization: ensure caller may record midterm progress for target user
        $targetUser = User::findOrFail($data['user_id']);
        if (!Gate::allows('viewMidterm', $targetUser)) {
            return back()->withErrors(['message' => 'Unauthorized to record midterm progress for this user.']);
        }

        // If a specific objective is provided, ensure caller may enter achieved values for it
        if (!empty($data['objective_id'])) {
            $objective = Objective::find($data['objective_id']);
            if ($objective) {
                $this->authorize('enterAchieved', $objective);
            }
        }

        $entry = MidtermProgress::create([
            'user_id' => $data['user_id'],
            'objective_id' => $data['objective_id'] ?? null,
            'financial_year' => $data['financial_year'] ?? null,
            'progress_percent' => (int) $data['progress_percent'],
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $user->id,
            'recorded_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'midterm_progress_recorded',
            'table_name' => 'midterm_progress',
            'record_id' => $entry->id,
            'details' => "Midterm progress recorded for user {$entry->user_id} ({$entry->progress_percent}%)",
        ]);

        return back()->with('success', 'Midterm progress recorded.');
    }

    /**
     * Return latest midterm progress entries for a user (AJAX).
     */
    public function getLatestMidterm(Request $request, $user_id): JsonResponse
    {
        $current = auth()->user();

        // Authorization: line manager of the user, HR admin, or super admin
        $target = User::findOrFail($user_id);
        if (!Gate::allows('viewMidterm', $target)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fy = $request->get('fy');

        $query = MidtermProgress::where('user_id', $user_id);
        if ($fy) $query->where('financial_year', $fy);

        $latest = $query->orderByDesc('recorded_at')->take(10)->get();

        return response()->json(['data' => $latest]);
    }
    public function showSetForUser($user_id)
    {
        $employee = User::findOrFail($user_id);
        $this->authorize('manageObjectivesFor', $employee);
        return view('appraisal.line_manager.set_objectives', compact('employee'));
    }
    public function setForUser(ObjectiveSettingRequest $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        $this->authorize('manageObjectivesFor', $employee);

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            $fyName = $fyService->label();
            if (!$this->isCreationAllowed($fyName)) {
                return back()->withErrors(['message' => 'Objective creation is not allowed at this time (outside allowed window).']);
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

        // Enforce creation window for departmental inline creation
        if (!$this->isCreationAllowed($data['financial_year'])) {
            return back()->withErrors(['message' => 'Cannot create departmental objectives at this time (outside allowed creation window).'])->withInput();
        }

        $user = auth()->user();
        $departmentUsers = User::where('department_id', $user->department_id)->where('is_active', true)->get();

        foreach ($departmentUsers as $u) {
            Objective::create([
                'user_id' => $u->id,
                'department_id' => $u->department_id,
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

        $count = Objective::where('department_id', $user->department_id)
            ->where('financial_year', $data['financial_year'])
            ->where('description', $data['description'])
            ->count();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'departmental_objective_inline_created',
            'table_name' => 'objectives',
            'record_id' => null,
            'details' => "Inline departmental objective created for department {$user->department_id} FY {$data['financial_year']} (applied to {$count} users)",
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
        $current = auth()->user();
        $canApprove = in_array($current->role, ['hr_admin', 'super_admin']) || ($current->role === 'line_manager' && $employee->line_manager_id === $current->id);

        // Get all financial years from database (use label)
        $years = FinancialYear::orderBy('start_date')->get()->pluck('label')->toArray();

        return view('appraisal.objectives.user_index', compact('employee', 'objectives', 'financialYear', 'years', 'canManage', 'canApprove'));
    }

    public function createForUser(Request $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        $this->authorize('manageObjectivesFor', $employee);

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            $fyName = $fyService->label();
            if (!$this->isCreationAllowed($fyName)) {
                return back()->withErrors(['message' => 'Objective creation is not allowed at this time (outside allowed window).']);
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
        $this->authorize('manageObjectivesFor', $employee);

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            $fyName = $fyService->label();
            if (!$this->isCreationAllowed($fyName)) {
                return back()->withErrors(['message' => 'Objective creation is not allowed at this time (outside allowed window).']);
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
        $created = Objective::create([
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
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'objective_created_for_user',
            'table_name' => 'objectives',
            'record_id' => $created->id,
            'details' => "Objective created for user {$employee->id}: {$created->description} (ID {$created->id})",
        ]);
        return redirect()->route('users.objectives.index', ['user_id' => $employee->id, 'fy' => $data['financial_year']])
            ->with('success', 'Objective added.');
    }

    public function editForUser(Request $request, $user_id, Objective $objective)
    {
        $employee = User::findOrFail($user_id);
        $this->authorize('manageObjectivesFor', $employee);
        if ($objective->user_id !== $employee->id) {
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
        $this->authorize('manageObjectivesFor', $employee);
        if ($objective->user_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            $fyName = $fyService->label();
            if (!$this->isCreationAllowed($fyName)) {
                return back()->withErrors(['message' => 'Objective modifications are not allowed at this time (outside allowed window).']);
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
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'objective_updated_for_user',
            'table_name' => 'objectives',
            'record_id' => $objective->id,
            'details' => "Objective updated for user {$employee->id}: {$objective->description} (ID {$objective->id})",
        ]);
        return redirect()->route('users.objectives.index', ['user_id' => $employee->id, 'fy' => $data['financial_year']])
            ->with('success', 'Objective updated.');
    }

    public function destroyForUser(Request $request, $user_id, Objective $objective)
    {
        $employee = User::findOrFail($user_id);
        $this->authorize('manageObjectivesFor', $employee);
        if ($objective->user_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $activeFY = FinancialYear::getActive();
        if ($activeFY && !$activeFY->isRevisionAllowed()) {
            return back()->withErrors(['message' => 'Objective deletions are locked after the 9th month of the financial year.']);
        }

        $fy = $objective->financial_year;
        $objId = $objective->id;
        $desc = $objective->description;
        $objective->delete();
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'objective_deleted_for_user',
            'table_name' => 'objectives',
            'record_id' => $objId,
            'details' => "Objective deleted for user {$employee->id}: {$desc} (ID {$objId})",
        ]);
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

    /**
     * Determine whether creation of objectives is allowed for the given financial year.
     * Rules:
     * - If current date is within first 6 months from FY start -> allow until 6-month review date.
     * - If past the 6-month review -> allow until the 9-month revision cutoff.
     */
    private function isCreationAllowed(string $financialYear): bool
    {
        // Try to resolve FY model first
        $fy = FinancialYear::where('label', $financialYear)->first();
        if ($fy && !empty($fy->start_date)) {
            try {
                $start = \Carbon\Carbon::parse($fy->start_date)->startOfDay();
                $sixMonth = (clone $start)->addMonths(6)->endOfDay();
                $nineMonth = (clone $start)->addMonths(9)->endOfDay();
                if (now()->greaterThan($sixMonth)) {
                    return now()->lessThanOrEqualTo($nineMonth);
                }
                return now()->lessThanOrEqualTo($sixMonth);
            } catch (\Throwable $e) {
                return false;
            }
        }

        // fallback: try to parse label like '2025-26' using the first year
        try {
            [$startYear] = explode('-', $financialYear);
            $start = \Carbon\Carbon::parse($startYear . '-07-01')->startOfDay();
            $sixMonth = (clone $start)->addMonths(6)->endOfDay();
            $nineMonth = (clone $start)->addMonths(9)->endOfDay();
            if (now()->greaterThan($sixMonth)) {
                return now()->lessThanOrEqualTo($nineMonth);
            }
            return now()->lessThanOrEqualTo($sixMonth);
        } catch (\Throwable $e) {
            return false;
        }
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
        // Support either single objective payload (legacy) or an array of objectives (preferred)
        $input = $request->all();

        // If objectives array present, remove empty rows (e.g., rows rendered by JS but left blank)
        if (!empty($input['objectives']) && is_array($input['objectives'])) {
            $filtered = [];
            foreach ($input['objectives'] as $o) {
                $desc = isset($o['description']) ? trim((string)$o['description']) : '';
                $weight = isset($o['weightage']) ? trim((string)$o['weightage']) : '';
                $target = isset($o['target']) ? trim((string)$o['target']) : '';
                // consider row non-empty if any of the main fields is filled
                if ($desc !== '' || $weight !== '' || $target !== '') {
                    $filtered[] = [
                        'description' => $desc,
                        'weightage' => $weight,
                        'target' => $target,
                        'is_smart' => isset($o['is_smart']) ? (bool)$o['is_smart'] : null,
                    ];
                }
            }
            $input['objectives'] = array_values($filtered);
        }

        // Determine validation rules based on whether objectives array has items after filtering
        if (!empty($input['objectives']) && is_array($input['objectives']) && count($input['objectives']) > 0) {
            $rules = [
                'department_id' => 'required|exists:departments,id',
                'objectives' => 'required|array|min:2|max:3',
                'objectives.*.description' => 'required|string',
                'objectives.*.weightage' => 'required|integer|in:10,15,20,25,30',
                'objectives.*.target' => 'required|string',
                'is_smart' => 'nullable|boolean',
                'financial_year' => 'required|string',
            ];
        } else {
            $rules = [
                'department_id' => 'required|exists:departments,id',
                'objectives' => 'nullable|array',
                'description' => 'required_without:objectives|string',
                'weightage' => 'required_without:objectives|integer|in:10,15,20,25,30',
                'target' => 'required_without:objectives|string',
                'is_smart' => 'nullable|boolean',
                'financial_year' => 'required|string',
            ];
        }

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // If the current user is a line manager, restrict department to their own
        $current = auth()->user();
        if ($current->role === 'line_manager') {
            $data['department_id'] = $current->department_id;
        }

        $fy = $data['financial_year'];

        // Ensure creation window permits creating departmental objectives for this FY
        if (!empty($data['objectives']) && is_array($data['objectives'])) {
            // For departmental objectives being created in bulk, enforce creation window
            if (!$this->isCreationAllowed($fy)) {
                return back()->withInput()->withErrors(['message' => 'Cannot create departmental objectives at this time (outside allowed creation window).']);
            }
        }

        // If an objectives array provided, ensure count between 2 and 3 and total weight == 30
        if (!empty($data['objectives']) && is_array($data['objectives'])) {
            $count = count($data['objectives']);
            if ($count < 2 || $count > 3) {
                return back()->withInput()->withErrors(['objectives' => 'Departmental objectives must be between 2 and 3 items.']);
            }
            $sum = array_sum(array_column($data['objectives'], 'weightage'));
            if ($sum != 30) {
                return back()->withInput()->withErrors(['weightage' => 'The total weightage of departmental objectives must equal 30%.']);
            }

            $departmentUsers = User::where('department_id', $data['department_id'])->where('is_active', true)->get();
            $skipped = [];
            foreach ($departmentUsers as $user) {
                $existingIndividual = Objective::where('user_id', $user->id)->where('type', 'individual')->where('financial_year', $fy)->sum('weightage');
                if ($existingIndividual + $sum > 100) {
                    $skipped[] = $user->name;
                    continue;
                }
                foreach ($data['objectives'] as $o) {
                    Objective::create([
                        'user_id' => $user->id,
                        'department_id' => $data['department_id'],
                        'type' => 'departmental',
                        'description' => $o['description'],
                        'weightage' => (int) $o['weightage'],
                        'target' => $o['target'],
                        'is_smart' => $o['is_smart'] ?? true,
                        'status' => 'set',
                        'financial_year' => $fy,
                        'created_by' => auth()->id(),
                    ]);
                }
            }
            $msg = 'Departmental objectives created.';
            if (!empty($skipped)) $msg .= ' Some users were skipped: ' . implode(', ', $skipped);
            return redirect()->route('team.objectives.index')->with('success', $msg);
        }

        // Legacy single-objective flow; ensure we don't exceed 3 departmental objectives
        $existingCount = Objective::where('type', 'departmental')->where('department_id', $data['department_id'])->where('financial_year', $fy)->count();
        if ($existingCount >= 3) {
            return back()->withInput()->withErrors(['objectives' => 'Department already has maximum of 3 departmental objectives for this financial year.']);
        }

        // Check total weightage for team objectives in this department/FY <= 30%
        $totalWeight = Objective::where('type', 'team')
            ->where('department_id', $data['department_id'])
            ->where('financial_year', $fy)
            ->sum('weightage');

        if ($totalWeight + $data['weightage'] > 30) {
            return back()->withInput()->withErrors(['weightage' => 'Total team objectives weightage cannot exceed 30% for this department in this financial year.']);
        }

        // Get all users in the selected department
        $departmentUsers = User::where('department_id', $data['department_id'])->where('is_active', true)->get();

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
                'financial_year' => $fy,
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

        // Enforce creation/edit window for team objectives
        if (!$this->isCreationAllowed($data['financial_year'])) {
            return back()->withInput()->withErrors(['message' => 'Team objective modifications are not allowed at this time (outside allowed window).']);
        }

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
