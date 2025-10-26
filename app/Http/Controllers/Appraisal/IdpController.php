<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Idp;

class IdpController extends Controller
{
    public function __construct()
    {
        // Wire the Idp policy to this controller so policy methods are applied
        // for resource actions and explicit checks.
        $this->authorizeResource(\App\Models\Idp::class, 'idp');
    }
    // Resource CRUD for super admin/HR admin
    public function index()
    {
        // For super admin/HR admin, show all IDPs
        if (auth()->user()->role === 'super_admin' || auth()->user()->role === 'hr_admin') {
            $idps = Idp::with('user')->orderByDesc('id')->get();
            return view('appraisal.super_admin.idps_index', compact('idps'));
        }
        // For regular users, show only their own IDPs
        $idps = auth()->user()->idps;
        return view('appraisal.idp.index', compact('idps'));
    }

    public function create()
    {
        $users = \App\Models\User::all();
        return view('appraisal.super_admin.idps_create', compact('users'));
    }

    public function store(Request $request)
    {
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
        Idp::create($data);
        return redirect()->route('idps.index')->with('success', 'IDP created successfully');
    }

    public function show(Idp $idp)
    {
        // Ensure the current user is authorized to view this IDP
        $this->authorize('view', $idp);
        return view('appraisal.super_admin.idps_show', compact('idp'));
    }

    public function update(Request $request, Idp $idp)
    {
        // Ensure the current user is authorized to update this IDP
        $this->authorize('update', $idp);
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
        $idp->update($updateData);
        return redirect()->route('idps.show', $idp)->with('success', 'IDP updated successfully');
    }

    public function destroy(Idp $idp)
    {
        // Authorize delete using IdpPolicy::delete
        $this->authorize('delete', $idp);
        $idp->delete();
        return redirect()->route('idps.index')->with('success', 'IDP deleted.');
    }

    public function edit(Idp $idp)
    {
        $users = \App\Models\User::all();
        return view('appraisal.super_admin.idps_edit', compact('idp', 'users'));
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
