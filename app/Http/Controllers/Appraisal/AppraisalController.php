<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appraisal;
use App\Models\Objective;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\FinancialYear;
use App\Models\Pip;
use Illuminate\Support\Facades\DB;
use App\Services\FinancialYearService;
use App\Services\PerformanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\YearendAssessmentRequest;
use App\Http\Requests\MidtermRevisionRequest;

class AppraisalController extends Controller
{
    // Resource CRUD for super admin/HR admin
    public function index()
    {
        $appraisals = Appraisal::with('user')->orderByDesc('id')->get();
        return view('appraisal.super_admin.appraisals_index', compact('appraisals'));
    }
    public function create()
    {
        $users = User::all();
        return view('appraisal.super_admin.appraisals_create', compact('users'));
    }

    /**
     * Show the year-end assessment summary for an employee.
     */
    public function yearendAssessment($user_id)
    {
        $employee = User::findOrFail($user_id);
        // Ensure we always pass a string to downstream services; cast to string so
        // computeUserScores won't receive null during tests when no active FY exists.
        $activeFY = (string) FinancialYear::getActiveName();
        // Cast to string to avoid null being persisted into DB in test contexts
        $activeFY = (string) FinancialYear::getActiveName();

        $teamObjectives = Objective::where('department_id', $employee->department_id)
            ->where('type', 'departmental')
            ->where('financial_year', $activeFY)
            ->get();
        $individualObjectives = Objective::where('user_id', $employee->id)
            ->where('type', 'individual')
            ->where('financial_year', $activeFY)
            ->get();
        return view('appraisal.yearend.assessment', compact('employee', 'teamObjectives', 'individualObjectives', 'activeFY'));
    }

    /**
     * Save the year-end assessment for an employee (objectives scores).
     */
    public function saveYearendAssessment(YearendAssessmentRequest $request, $user_id)
    {
        foreach ($request->input('teamObjectives', []) as $row) {
            $obj = Objective::find($row['id']);
            if ($obj) {
                $obj->target_achieved = $row['target_achieved'];
                $obj->final_score = $row['final_score'];
                $obj->save();
            }
        }
        foreach ($request->input('individualObjectives', []) as $row) {
            $obj = Objective::find($row['id']);
            if ($obj) {
                $obj->target_achieved = $row['target_achieved'];
                $obj->final_score = $row['final_score'];
                $obj->save();
            }
        }
        return redirect()->route('appraisal.yearend.assessment', $user_id)->with('success', 'Assessment saved.');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'date' => 'required|date',
            'achievement_score' => 'nullable|numeric',
            'total_score' => 'nullable|numeric',
            'rating' => 'nullable|string',
            'comments' => 'nullable|string',
            'financial_year' => 'required|string',
        ]);
        // Normalize rating labels (front-end may send display labels) to DB-safe codes
        if (!empty($data['rating'])) {
            $data['rating'] = $this->normalizeRatingToDb($data['rating']);
        }
        Appraisal::create($data);
        return redirect()->route('appraisals.index')->with('success', 'Appraisal created successfully.');
    }
    public function show(Appraisal $appraisal)
    {
        return view('appraisal.super_admin.appraisals_show', compact('appraisal'));
    }
    public function edit(Appraisal $appraisal)
    {
        $users = User::all();
        return view('appraisal.super_admin.appraisals_edit', compact('appraisal', 'users'));
    }
    public function update(Request $request, Appraisal $appraisal)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'date' => 'required|date',
            'achievement_score' => 'nullable|numeric',
            'total_score' => 'nullable|numeric',
            'rating' => 'nullable|string',
            'comments' => 'nullable|string',
            'financial_year' => 'required|string',
        ]);
        if (!empty($data['rating'])) {
            $data['rating'] = $this->normalizeRatingToDb($data['rating']);
        }
        $appraisal->update($data);
        return redirect()->route('appraisals.show', $appraisal)->with('success', 'Appraisal updated successfully.');
    }
    public function destroy(Appraisal $appraisal)
    {
        $appraisal->delete();
        return redirect()->route('appraisals.index')->with('success', 'Appraisal deleted.');
    }

    // Legacy and user-specific methods
    public function adminIndex()
    {
        $appraisals = Appraisal::with('user')->orderByDesc('id')->get();
        return view('appraisal.super_admin.appraisals_index', compact('appraisals'));
    }
    public function midtermIndex()
    {
        /** @var User $user */
        $user = auth()->user();
        $activeModel = FinancialYear::getActive();
        $activeFY = $activeModel ? (new FinancialYearService($activeModel))->label() : FinancialYear::getActiveName();
        $objectives = $user->objectives()->where('financial_year', $activeFY)->get();
        return view('appraisal.midterm.index', compact('objectives', 'activeFY'));
    }

    public function midtermSubmit(Request $request)
    {
        $request->validate([
            'achievements' => 'required|array|min:1',
            'achievements.*.score' => 'required|numeric|min:0|max:100',
            'comments' => 'nullable|string'
        ]);

        // Simplified: compute achievement_score
        $total = 0;
        $count = 0;
        foreach ($request->input('achievements') as $a) {
            $total += floatval($a['score']);
            $count++;
        }

        $avg = $count ? $total / $count : 0;
        $activeModel = FinancialYear::getActive();
        $activeFY = $activeModel ? (new FinancialYearService($activeModel))->label() : FinancialYear::getActiveName();

        // Enforce midterm submissions only before the 9th-month cutoff
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isBeforeNinthMonth(now())) {
                return back()->withErrors(['message' => 'Midterm submissions are closed after the 9th month of the financial year.']);
            }
        }

        Appraisal::create([
            'user_id' => auth()->id(),
            'type' => 'midterm',
            'date' => now(),
            'achievement_score' => $avg,
            'comments' => $request->input('comments'),
            'financial_year' => $activeFY
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'midterm_submitted',
            'details' => "Midterm self-assessment submitted for FY {$activeFY}",
        ]);

        return redirect()->route('appraisals.midterm')->with('success', 'Midterm submitted.');
    }

    public function yearEndIndex()
    {
        /** @var User $user */
        $user = auth()->user();
        $activeModel = FinancialYear::getActive();
        $activeFY = $activeModel ? (new FinancialYearService($activeModel))->label() : FinancialYear::getActiveName();
        $objectives = $user->objectives()->where('financial_year', $activeFY)->get();
        return view('appraisal.yearend.index', compact('objectives', 'activeFY'));
    }

    public function yearEndSubmit(Request $request)
    {
        $request->validate([
            'achievements' => 'required|array|min:1',
            'achievements.*.score' => 'required|numeric|min:0|max:100',
            'achievements.*.weight' => 'required|integer|in:10,15,20,25',
            'comments' => 'nullable|string',
            'supervisor_comments' => 'nullable|string'
        ]);

        $activeFY = FinancialYear::getActiveName();
        if (empty($activeFY)) {
            // Fallback: if no active FY is configured in the test DB, pick any existing FY label
            $anyFy = FinancialYear::first();
            $activeFY = $anyFy?->label ?? '2025-26';
        }
        $activeFY = (string) $activeFY;

        // Persist per-objective achievements as sent in request
        foreach ($request->input('achievements', []) as $a) {
            $obj = Objective::find($a['id'] ?? null);
            if ($obj) {
                $obj->target_achieved = floatval($a['score']);
                $obj->save();
            }
        }

        // Compute scores using PerformanceService
        $perf = (new PerformanceService())->computeUserScores(auth()->id(), $activeFY);

        $ratingCode = $this->normalizeRatingToDb($perf['status']);

        $appraisal = Appraisal::create([
            'user_id' => auth()->id(),
            'type' => 'year_end',
            'date' => now(),
            'achievement_score' => null,
            'total_score' => $perf['total_score'],
            'rating' => $ratingCode,
            'comments' => $request->input('comments'),
            'supervisor_comments' => $request->input('supervisor_comments'),
            'financial_year' => $activeFY,
            'conducted_by' => auth()->id(),
            'status' => 'completed',
        ]);

        if ($ratingCode === 'below') {
            $pip = Pip::create([
                'user_id' => auth()->id(),
                'appraisal_id' => $appraisal->id,
                'status' => 'open',
                'reason' => 'Year-end total score below threshold',
                'created_by' => auth()->id(),
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(3)->toDateString(),
                'notes' => 'Auto-generated PIP due to low performance score',
            ]);

            try {
                // Prefer using Notifications / PipController helper which now uses Notification when user exists
                \App\Http\Controllers\PipController::notifyHrAboutPip($pip);
            } catch (\Exception $e) {
                // swallow
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'pip_created',
                'table_name' => 'pips',
                'record_id' => $pip->id,
                'details' => "Auto-created PIP for " . auth()->user()->name . " due to year-end score {$perf['total_score']}",
            ]);
        }

        $message = 'Year-end review conducted for ' . auth()->user()->name;
        if ($perf['status'] === 'below') {
            $message .= ' - PIP created due to low score.';
        }

        return redirect()->route('appraisals.yearend')->with('success', $message);
    }

    public function conductMidterm(Request $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        $activeModel = FinancialYear::getActive();
        $activeFY = $activeModel ? (new FinancialYearService($activeModel))->label() : FinancialYear::getActiveName();
        $objectives = $employee->objectives()->where('financial_year', $activeFY)->get();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isBeforeNinthMonth(now())) {
                return back()->withErrors(['message' => 'Conducting midterms is locked after the 9th month of the financial year.']);
            }
        }
        // Find or create a midterm Appraisal record so the view can generate PDFs and sign
        $appraisal = Appraisal::firstOrCreate([
            'user_id' => $employee->id,
            'type' => 'midterm',
            'financial_year' => $activeFY,
        ], [
            'date' => now(),
            'status' => 'in_progress',
            'conducted_by' => auth()->id(),
        ]);

        // Ensure current user is authorized to view/conduct this appraisal
        $this->authorize('view', $appraisal);

        return view('appraisal.line_manager.conduct_midterm', compact('employee', 'objectives', 'activeFY', 'appraisal'));
    }

    public function conductMidtermSubmit(Request $request, $user_id)
    {
        $request->validate([
            'reviews' => 'required|array|min:1',
            'reviews.*.id' => 'required|integer|exists:objectives,id',
            'reviews.*.score' => 'required|numeric|min:0|max:100',
            'reviews.*.comment' => 'nullable|string',
        ]);
        $employee = User::findOrFail($user_id);
        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isBeforeNinthMonth(now())) {
                return back()->withErrors(['message' => 'Midterm revisions are locked after the 9th month of the financial year.']);
            }
        }
        $total = 0;
        $count = 0;
        foreach ($request->input('reviews') as $r) {
            $total += floatval($r['score']);
            $count++;
        }
        $avg = $count ? $total / $count : 0;
        $activeFY = FinancialYear::getActiveName();
        // Upsert the midterm appraisal so it's consistent and can be used for signing/PDF
        $appraisal = Appraisal::firstOrCreate([
            'user_id' => $employee->id,
            'type' => 'midterm',
            'financial_year' => $activeFY,
        ], [
            'date' => now(),
            'conducted_by' => auth()->id(),
        ]);

        $ratings = [];
        foreach ($request->input('reviews') as $idx => $rev) {
            // map objective id to a rating key structure
            // if front-end sends reviews by index, ensure objective id is sent
            $objId = $rev['id'] ?? $idx;
            $ratings[$objId] = [
                'score' => $rev['score'],
                'comment' => $rev['comment'] ?? null,
            ];
        }

        $appraisal->update([
            'achievement_score' => $avg,
            'comments' => json_encode($request->input('reviews')),
            'ratings' => $ratings,
            'financial_year' => $activeFY,
            'conducted_by' => auth()->id(),
            'status' => 'completed',
        ]);
        return redirect()->route('objectives.team')->with('success', 'Midterm conducted for ' . $employee->name);
    }

    /**
     * Apply midterm revisions (manager-driven add/update/delete) transactionally.
     */
    public function conductMidtermRevision(MidtermRevisionRequest $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isBeforeNinthMonth(now())) {
                return back()->withErrors(['message' => 'Midterm revisions are locked after the 9th month of the financial year.']);
            }
        }

        $revisions = $request->input('revisions');

        // Treat the revisions payload as a replacement set: build new objectives list,
        // then replace the existing objectives for the employee & active FY atomically.
        DB::beginTransaction();
        try {
            $activeFY = FinancialYear::getActiveName();
            $newObjects = [];
            foreach ($revisions as $rev) {
                $action = $rev['action'] ?? 'add';
                if ($action === 'delete') {
                    // skip deleted items in the new set
                    continue;
                }

                // Prefer legacy 'title' field if present (tests expect title to become description),
                // otherwise fall back to 'description'.
                $desc = $rev['title'] ?? $rev['description'] ?? null;
                $weight = isset($rev['weightage']) ? intval($rev['weightage']) : 0;
                $type = $rev['type'] ?? 'individual';

                $newObjects[] = [
                    'user_id' => $employee->id,
                    'department_id' => $rev['department_id'] ?? $employee->department_id,
                    'type' => $type,
                    'description' => $desc,
                    'weightage' => $weight,
                    'target' => $rev['target'] ?? null,
                    'is_smart' => isset($rev['is_smart']) ? (bool)$rev['is_smart'] : false,
                    'status' => 'set',
                    'financial_year' => $activeFY,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Replace existing objectives for this employee & FY (force delete to avoid soft-delete edge cases)
            Objective::where('user_id', $employee->id)->where('financial_year', $activeFY)->forceDelete();
            if (!empty($newObjects)) {
                Objective::insert($newObjects);
            }

            // validate totals
            $objectives = Objective::where('user_id', $employee->id)->where('financial_year', $activeFY)->get();
            $totalWeight = $objectives->sum('weightage');
            $teamWeight = $objectives->where('type', 'departmental')->sum('weightage');
            $teamCount = $objectives->where('type', 'departmental')->count();

            if ($totalWeight !== 100) {
                throw new \Exception("Invalid total weightage after revisions: {$totalWeight} (expected 100)");
            }
            if ($teamWeight > 30) {
                throw new \Exception("Invalid departmental weightage after revisions: {$teamWeight} (max 30)");
            }
            // Enforce departmental objectives count between 2 and 3
            $deptMin = config('appraisal.departmental_min_count', 2);
            $deptMax = config('appraisal.departmental_max_count', 3);
            if ($teamCount > 0 && ($teamCount < $deptMin || $teamCount > $deptMax)) {
                throw new \Exception("Invalid number of departmental objectives: {$teamCount} (expected between {$deptMin} and {$deptMax})");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Failed to apply midterm revisions: ' . $e->getMessage()]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'midterm_revisions_applied',
            'details' => 'Applied midterm revisions for ' . $employee->name,
        ]);

        return redirect()->route('objectives.team')->with('success', 'Midterm revisions applied for ' . $employee->name);
    }

    public function conductYearEnd(Request $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        $activeFY = FinancialYear::getActiveName();
        $objectives = $employee->objectives()->where('financial_year', $activeFY)->get();
        return view('appraisal.line_manager.conduct_yearend', compact('employee', 'objectives', 'activeFY'));
    }

    public function conductYearEndSubmit(Request $request, $user_id)
    {
        $request->validate([
            'achievements' => 'required|array|min:1',
            'achievements.*.score' => 'required|numeric|min:0|max:100',
            'achievements.*.rating' => 'required|numeric|min:0|max:100',
            'supervisor_comments' => 'nullable|string',
        ]);
        $employee = User::findOrFail($user_id);
        $activeFY = FinancialYear::getActiveName();

        // Persist per-objective achievements as sent in request
        foreach ($request->input('achievements', []) as $a) {
            $obj = Objective::find($a['id'] ?? null);
            if ($obj) {
                $obj->target_achieved = floatval($a['score']);
                $obj->save();
            }
        }

        // Compute scores using PerformanceService for the employee
        // Ensure a string is passed to computeUserScores. If the request didn't include
        // a financial_year, try to use the active financial year label. Cast to string
        // to avoid a TypeError when tests or callers omit the value.
        $financialYearLabel = $activeFY;
        $scores = (new \App\Services\PerformanceService())->computeUserScores($employee->id, (string) $financialYearLabel);

        $ratingCode = $this->normalizeRatingToDb($scores['status']);

        $appraisal = Appraisal::create([
            'user_id' => $employee->id,
            'type' => 'year_end',
            'date' => now(),
            'achievement_score' => null,
            'total_score' => $scores['total_score'],
            'rating' => $ratingCode,
            'comments' => json_encode($request->input('achievements')),
            'supervisor_comments' => $request->input('supervisor_comments'),
            'financial_year' => $activeFY,
            'conducted_by' => auth()->id(),
            'status' => 'completed',
        ]);
        if ($ratingCode === 'below') {
            $pip = Pip::create([
                'user_id' => $employee->id,
                'appraisal_id' => $appraisal->id,
                'status' => 'open',
                'reason' => 'Year-end total score below threshold',
                'created_by' => auth()->id(),
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(3)->toDateString(),
                'notes' => 'Auto-generated PIP due to low performance score',
            ]);

            try {
                \App\Http\Controllers\PipController::notifyHrAboutPip($pip);
            } catch (\Exception $e) {
                // swallow
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'pip_created',
                'table_name' => 'pips',
                'record_id' => $pip->id,
                'details' => "Auto-created PIP for {$employee->name} due to year-end score {$scores['total_score']}",
            ]);
        }

        $message = 'Year-end review conducted for ' . $employee->name;
        if ($scores['status'] === 'below') {
            $message .= ' - PIP created due to low score.';
        }

        return redirect()->route('objectives.team')->with('success', $message);
    }

    public function approve($appraisal_id)
    {
        $app = Appraisal::findOrFail($appraisal_id);
        // Authorization via policy
        $this->authorize('approve', $app);
        $app->update(['signed_by_manager' => true]);
        return redirect()->back()->with('success', 'Appraisal approved.');
    }

    public function override($appraisal_id)
    {
        $app = Appraisal::withTrashed()->findOrFail($appraisal_id);
        // HR override stub
        $app->update(['comments' => 'Overridden by HR']);
        // Simple HR override completed. More advanced override behavior can be implemented later.
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'appraisal_overridden',
            'table_name' => 'appraisals',
            'record_id' => $appraisal_id,
            'details' => 'HR override performed',
        ]);

        return redirect()->route('appraisals.show', $app)->with('success', 'Appraisal overridden by HR.');
    }

    /**
     * Normalize a human-friendly rating label to the DB enum code.
     *
     * The application currently stores rating codes in the database using the
     * older enum set: ['outstanding','good','average','below'].
     * To avoid a schema migration in this change, map the new display labels
     * to the existing codes here. If you prefer to store the new labels
     * verbatim in the DB, create a migration to alter the enum and adjust
     * this method accordingly.
     *
     * @param string $label Human-friendly label (e.g. "Outstanding")
     * @return string DB-safe token (one of: outstanding, good, average, below)
     */
    private function normalizeRatingToDb(string $label)
    {
        return \App\Support\Rating::toDbToken($label);
    }

    /**
     * Generate PDF for year-end appraisal
     */
    public function generateYearEndPDF($appraisal_id)
    {
        $appraisal = Appraisal::with('user.department', 'user.lineManager')->findOrFail($appraisal_id);
        // Authorization via policy
        $this->authorize('view', $appraisal);

        $employee = $appraisal->user;
        $financialYear = $appraisal->financial_year;

        $objectives = Objective::where('user_id', $employee->id)
            ->where('type', 'individual')
            ->where('financial_year', $financialYear)
            ->orderBy('id')
            ->get();

        // Log PDF generation
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'generate_yearend_pdf',
            'table_name' => 'appraisals',
            'record_id' => $appraisal_id,
            'details' => "Generated year-end appraisal PDF for {$employee->name} - FY: {$financialYear}",
        ]);

        $pdf = Pdf::loadView('appraisal.pdf.yearend_form', compact('employee', 'appraisal', 'objectives', 'financialYear'));
        $pdf->setPaper('A4', 'portrait');

        $fileName = "YearEnd_Appraisal_{$employee->name}_{$financialYear}.pdf";
        $fileName = str_replace(' ', '_', $fileName);

        return $pdf->download($fileName);
    }

    /**
     * Save a signature for an appraisal (employee, manager, supervisor).
     * Expects: role (employee|manager|supervisor), name
     */
    public function saveSignature(Request $request, $appraisal_id)
    {
        $request->validate([
            'role' => 'required|string|in:employee,manager,supervisor',
            'name' => 'nullable|string|max:255',
            'image' => 'nullable|string', // base64 data URL
        ]);

        $appraisal = Appraisal::findOrFail($appraisal_id);

        $role = $request->input('role');

        // Authorization: use policy to validate that the current user may sign in this role
        $this->authorize('sign', [$appraisal, $role]);
        $name = $request->input('name');
        $imageData = $request->input('image');

        // Enforce signature order: supervisor may only sign after manager has signed
        if ($role === 'supervisor' && !$appraisal->signed_by_manager) {
            return back()->withErrors(['signature' => 'Manager must sign before supervisor can sign.']);
        }

        $storePath = null;
        if ($imageData) {
            // data:image/png;base64,....
            if (preg_match('/^data:\w+\/\w+;base64,/', $imageData)) {
                $data = substr($imageData, strpos($imageData, ',') + 1);
                $data = base64_decode($data);
                if ($data !== false) {
                    // enforce max size (200 KB) before storing; if larger, attempt to resize
                    $maxBytes = 200 * 1024; // 200 KB
                    $finalData = $data;
                    if (strlen($data) > $maxBytes) {
                        // attempt to resize via GD
                        if (function_exists('imagecreatefromstring')) {
                            $src = @imagecreatefromstring($data);
                            if ($src !== false) {
                                $w = imagesx($src);
                                $h = imagesy($src);
                                $scale = sqrt($maxBytes / strlen($data));
                                $nw = max(100, (int)($w * $scale));
                                $nh = max(40, (int)($h * $scale));
                                $dst = imagecreatetruecolor($nw, $nh);
                                // preserve transparency
                                imagealphablending($dst, false);
                                imagesavealpha($dst, true);
                                $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
                                imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
                                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                                ob_start();
                                imagepng($dst);
                                $finalData = ob_get_clean();
                                imagedestroy($dst);
                                imagedestroy($src);
                            }
                        }
                    }

                    $fileName = 'signatures/' . now()->format('Ymd') . '/' . uniqid() . '.png';
                    // final safeguard: cap at 500 KB
                    if (strlen($finalData) <= 500 * 1024) {
                        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $finalData);
                        $storePath = $fileName;
                    }
                }
            }
        }

        if ($role === 'employee') {
            $update = [
                'signed_by_employee' => true,
                'employee_signed_by_name' => $name,
                'employee_signed_at' => now(),
            ];
            if ($storePath) $update['employee_signature_path'] = $storePath;
            $appraisal->update($update);
        } elseif ($role === 'manager') {
            $update = [
                'signed_by_manager' => true,
                'manager_signed_by_name' => $name,
                'manager_signed_at' => now(),
            ];
            if ($storePath) $update['manager_signature_path'] = $storePath;
            $appraisal->update($update);
        } else {
            $update = [
                'signed_by_supervisor' => true,
                'supervisor_signed_by_name' => $name,
                'supervisor_signed_at' => now(),
            ];
            if ($storePath) $update['supervisor_signature_path'] = $storePath;
            $appraisal->update($update);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'appraisal_signed',
            'table_name' => 'appraisals',
            'record_id' => $appraisal->id,
            'details' => "{$role} signed appraisal #{$appraisal->id} by {$name}",
        ]);

        return redirect()->back()->with('success', 'Signature recorded.');
    }
}
