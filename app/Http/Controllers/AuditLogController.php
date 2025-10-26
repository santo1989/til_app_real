<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('user')->orderByDesc('id')->get();
        return view('audit_logs.index', compact('logs'));
    }

    public function create()
    {
        $users = User::all();
        return view('audit_logs.form', ['log' => new AuditLog(), 'users' => $users, 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'action' => 'required|string|max:255',
            'details' => 'nullable|string',
        ]);
        $log = AuditLog::create($data);
        return redirect()->route('audit-logs.show', $log)->with('success', 'Audit log created.');
    }

    public function show(AuditLog $audit_log)
    {
        $audit_log->load('user');
        return view('audit_logs.show', ['log' => $audit_log]);
    }

    public function edit(AuditLog $audit_log)
    {
        $users = User::all();
        return view('audit_logs.form', ['log' => $audit_log, 'users' => $users, 'mode' => 'edit']);
    }

    public function update(Request $request, AuditLog $audit_log)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'action' => 'required|string|max:255',
            'details' => 'nullable|string',
        ]);
        $audit_log->update($data);
        return redirect()->route('audit-logs.show', $audit_log)->with('success', 'Audit log updated.');
    }

    public function destroy(AuditLog $audit_log)
    {
        $audit_log->delete();
        return redirect()->route('audit-logs.index')->with('success', 'Audit log deleted.');
    }
}
