<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Idp;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class IdpController extends Controller
{
    public function __construct()
    {
        // Wire the Idp policy to this controller so policy methods are applied
        // for resource actions and explicit checks.
        // Note: switched to explicit in-method authorization to allow
        // better debugging and to avoid early middleware 403 that prevented
        // our debug logs from running.
    }
    // Resource CRUD for super admin/HR admin
    public function index()
    {
        // Use policy to decide whether the actor should see the admin index
        if (Gate::allows('viewAny', Idp::class)) {
            $idps = Idp::with('user')->orderByDesc('id')->get();
            return view('appraisal.super_admin.idps_index', compact('idps'));
        }

        // For regular users, show only their own IDPs
        $idps = auth()->user()->idps;
        return view('appraisal.idp.index', compact('idps'));
    }

    public function create()
    {
        // Explicit authorization: ensure actor can create an IDP
        $this->authorize('create', Idp::class);

        // Show admin create view when actor can viewAny IDPs; otherwise show regular create view
        if (Gate::allows('viewAny', Idp::class)) {
            $users = \App\Models\User::all();
            return view('appraisal.super_admin.idps_create', compact('users'));
        }

        return view('appraisal.idp.create');
    }

    public function store(Request $request)
    {
        // Explicit authorization: ensure actor can create an IDP
        $this->authorize('create', Idp::class);

        // Ensure revisions are allowed for the active financial year
        $fy = \App\Models\FinancialYear::active();
        if ($fy && !$fy->isRevisionAllowed()) {
            return redirect()->back()->with('error', 'IDP revisions are closed for the active financial year.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'description' => 'required|string',
            'review_date' => 'required|date',
            'progress_till_dec' => 'nullable|string',
            'revised_description' => 'nullable|string',
            'accomplishment' => 'nullable|string',
            'status' => 'nullable|string',
            'signed_by_employee' => 'nullable|boolean',
            'employee_signed_by_name' => 'nullable|string',
            'employee_signed_at' => 'nullable|date',
            'employee_signature_path' => 'nullable|string',
            'signed_by_manager' => 'nullable|boolean',
            'manager_signed_by_name' => 'nullable|string',
            'manager_signed_at' => 'nullable|date',
            'manager_signature_path' => 'nullable|string',
        ]);
        $data = $request->only([
            'user_id',
            'description',
            'review_date',
            'progress_till_dec',
            'revised_description',
            'accomplishment',
            'status',
            'signed_by_employee',
            'employee_signed_by_name',
            'employee_signed_at',
            'employee_signature_path',
            'signed_by_manager',
            'manager_signed_by_name',
            'manager_signed_at',
            'manager_signature_path'
        ]);
        // Role-based default approval behavior
        $user = auth()->user();
        if ($user->role === 'employee') {
            // employees may only create for themselves
            if ((int)$data['user_id'] !== $user->id) {
                abort(403, 'Employees may only create their own IDPs.');
            }
            $data['is_approved'] = false;
            $data['approved_by_id'] = null;
            $data['approved_at'] = null;
            $data['approved_by_role'] = null;
        } elseif (in_array($user->role, ['line_manager', 'hr_admin', 'super_admin'])) {
            // these roles can create and auto-approve
            $data['is_approved'] = true;
            $data['approved_by_id'] = $user->id;
            $data['approved_at'] = now();
            $data['approved_by_role'] = $user->role;
        }

        $idp = Idp::create($data);
        // Debug log: capture who created and whether they have admin-level IDP view permissions
        try {
            Log::info('idp.store.debug', [
                'auth_id' => auth()->id(),
                'gate_viewAny' => Gate::forUser(auth()->user())->allows('viewAny', Idp::class),
                'idp_id' => $idp->id,
            ]);
        } catch (\Throwable $e) {
            // Non-fatal: keep creating even if logging fails
        }
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_created',
            'table_name' => 'idps',
            'record_id' => $idp->id,
            'details' => "IDP created for user_id {$idp->user_id} (ID {$idp->id})",
        ]);
        // Redirect based on policy (admins see idps.*, regular users see idp.*)
        if (Gate::allows('viewAny', Idp::class)) {
            return redirect()->route('idps.index')->with('success', 'IDP created successfully');
        }
        return redirect()->route('idp.index')->with('success', 'IDP created successfully');
    }

    public function show(Idp $idp)
    {
        // Ensure the current user is authorized to view this IDP
        $this->authorize('view', $idp);
        if (Gate::allows('viewAny', Idp::class)) {
            return view('appraisal.super_admin.idps_show', compact('idp'));
        }
        return view('appraisal.idp.show', compact('idp'));
    }

    public function update(Request $request, Idp $idp)
    {
        // Ensure the current user is authorized to update this IDP
        $this->authorize('update', $idp);
        // Ensure revisions are allowed for the active financial year
        $fy = \App\Models\FinancialYear::active();
        if ($fy && !$fy->isRevisionAllowed()) {
            return redirect()->back()->with('error', 'IDP revisions are closed for the active financial year.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'description' => 'required|string',
            'review_date' => 'required|date',
            'progress_till_dec' => 'nullable|string',
            'revised_description' => 'nullable|string',
            'accomplishment' => 'nullable|string',
            'status' => 'nullable|string',
            'signed_by_employee' => 'nullable|boolean',
            'employee_signed_by_name' => 'nullable|string',
            'employee_signed_at' => 'nullable|date',
            'employee_signature_path' => 'nullable|string',
            'signed_by_manager' => 'nullable|boolean',
            'manager_signed_by_name' => 'nullable|string',
            'manager_signed_at' => 'nullable|date',
            'manager_signature_path' => 'nullable|string',
        ]);
        // Record revision history: capture fields that changed
        // Note: 'status' is not a column on the idps table in current schema — exclude it
        $fields = ['user_id', 'description', 'review_date', 'progress_till_dec', 'revised_description', 'accomplishment'];
        $original = $idp->only($fields);
        $new = $request->only($fields);
        $changes = [];
        foreach ($fields as $f) {
            $origVal = $original[$f] ?? null;
            $newVal = $new[$f] ?? null;
            if ((string)$origVal !== (string)$newVal) {
                $changes[$f] = ['old' => $origVal, 'new' => $newVal];
            }
        }
        if (!empty($changes)) {
            \App\Models\IdpRevision::create([
                'idp_id' => $idp->id,
                'changes' => $changes,
                'changed_by' => auth()->id(),
            ]);
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'idp_revision_created',
                'table_name' => 'idp_revisions',
                'record_id' => $idp->id,
                'details' => "IDP revision created for IDP {$idp->id}",
            ]);
        }

        // Merge signature fields into update data if present
        $updateData = array_merge($new, $request->only([
            'signed_by_employee',
            'employee_signed_by_name',
            'employee_signed_at',
            'employee_signature_path',
            'signed_by_manager',
            'manager_signed_by_name',
            'manager_signed_at',
            'manager_signature_path'
        ]));
        // Role-based approvals/clearing: if employee edits their own IDP, reset approval
        $user = auth()->user();
        if ($user->role === 'employee' && $idp->user_id === $user->id) {
            $updateData['is_approved'] = false;
            $updateData['approved_by_id'] = null;
            $updateData['approved_at'] = null;
            $updateData['approved_by_role'] = null;
        } elseif (in_array($user->role, ['line_manager', 'hr_admin', 'super_admin'])) {
            // line_manager, hr_admin and super_admin edits are treated as approvals
            $updateData['is_approved'] = true;
            $updateData['approved_by_id'] = $user->id;
            $updateData['approved_at'] = now();
            $updateData['approved_by_role'] = $user->role;
        }

        $idp->update($updateData);
        // Debug log: capture who updated and whether they have admin-level IDP view permissions
        try {
            Log::info('idp.update.debug', [
                'auth_id' => auth()->id(),
                'gate_viewAny' => Gate::forUser(auth()->user())->allows('viewAny', Idp::class),
                'idp_id' => $idp->id,
            ]);
        } catch (\Throwable $e) {
        }
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_updated',
            'table_name' => 'idps',
            'record_id' => $idp->id,
            'details' => "IDP updated for IDP {$idp->id}",
        ]);
        if (Gate::allows('viewAny', Idp::class)) {
            return redirect()->route('idps.show', $idp)->with('success', 'IDP updated successfully');
        }
        return redirect()->route('idp.index')->with('success', 'IDP updated successfully');
    }

    public function destroy(Idp $idp)
    {
        $this->authorize('delete', $idp);
        $idpId = $idp->id;
        $idp->delete();
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_deleted',
            'table_name' => 'idps',
            'record_id' => $idpId,
            'details' => "IDP deleted: ID {$idpId}",
        ]);
        if (Gate::allows('viewAny', Idp::class)) {
            return redirect()->route('idps.index')->with('success', 'IDP deleted.');
        }
        return redirect()->route('idp.index')->with('success', 'IDP deleted.');
    }

    public function edit(Idp $idp)
    {
        $this->authorize('update', $idp);
        if (Gate::allows('viewAny', Idp::class)) {
            $users = \App\Models\User::all();
            return view('appraisal.super_admin.idps_edit', compact('idp', 'users'));
        }
        return view('appraisal.idp.edit', compact('idp'));
    }

    /**
     * Approve an IDP. Can be called by line_manager (for their reports), hr_admin, or super_admin.
     */
    public function approve(Request $request, Idp $idp)
    {
        $this->authorize('approve', $idp);
        $user = auth()->user();
        $idp->is_approved = true;
        $idp->approved_by_id = $user->id;
        $idp->approved_at = now();
        $idp->approved_by_role = $user->role;
        $idp->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'idp_approved',
            'table_name' => 'idps',
            'record_id' => $idp->id,
            'details' => "IDP approved by {$user->id} ({$user->role}) for IDP {$idp->id}",
        ]);

        return redirect()->back()->with('success', 'IDP approved');
    }

    // Legacy methods for compatibility
    public function adminIndex()
    {
        $idps = Idp::with('user')->orderByDesc('id')->get();
        return view('appraisal.super_admin.idps_index', compact('idps'));
    }

    public function revise(Request $request, $user_id)
    {
        // manager revises employee IDP
        return redirect()->back()->with('success', 'IDP revised.');
    }
}
