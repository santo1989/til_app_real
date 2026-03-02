<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pip;
use App\Models\User;
use App\Mail\PipCreatedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Department;
use App\Models\AuditLog;

class PipController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Pip::class, 'pip');
    }

    public function index()
    {
        // filters: status, start_date_from, start_date_to, department_id, manager_id
        $query = Pip::with('user');

        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('start_date_from')) {
            $query->whereDate('start_date', '>=', request('start_date_from'));
        }
        if (request('start_date_to')) {
            $query->whereDate('start_date', '<=', request('start_date_to'));
        }
        if (request('department_id')) {
            $query->whereHas('user', function ($q) {
                $q->where('department_id', request('department_id'));
            });
        }
        if (request('manager_id')) {
            $query->whereHas('user', function ($q) {
                $q->where('line_manager_id', request('manager_id'));
            });
        }

        $pips = $query->orderByDesc('created_at')->paginate(25)->appends(request()->query());
        // pass departments and managers for filter options
        $departments = Department::orderBy('name')->get();
        $managers = User::whereIn('role', ['line_manager', 'dept_head', 'super_admin'])->orderBy('name')->get();
        return view('pips.index', compact('pips', 'departments', 'managers'));
    }

    public function show($id)
    {
        $pip = Pip::with('user', 'appraisal')->findOrFail($id);
        return view('pips.show', compact('pip'));
    }

    public function create()
    {
        $users = User::where('role', 'employee')->get();
        return view('pips.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $data['status'] = 'open';
        $data['created_by'] = auth()->id();
        $pip = Pip::create($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'pip_created',
            'table_name' => 'pips',
            'record_id' => $pip->id,
            'details' => "PIP created for user_id {$pip->user_id} (ID {$pip->id})",
        ]);
        // notify
        try {
            self::notifyHrAboutPip($pip);
        } catch (\Exception $e) {
        }
        return redirect()->route('pips.index')->with('success', 'PIP created.');
    }

    public function edit($id)
    {
        $pip = Pip::findOrFail($id);
        $users = User::where('role', 'employee')->get();
        return view('pips.edit', compact('pip', 'users'));
    }

    public function update(Request $request, $id)
    {
        $pip = Pip::findOrFail($id);
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:open,closed'
        ]);
        $pip->update($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'pip_updated',
            'table_name' => 'pips',
            'record_id' => $pip->id,
            'details' => "PIP updated: ID {$pip->id}",
        ]);
        return redirect()->route('pips.show', $pip->id)->with('success', 'PIP updated.');
    }

    public function export()
    {
        // honor same filters as index
        $query = Pip::with('user');
        if (request('status')) $query->where('status', request('status'));
        if (request('start_date_from')) $query->whereDate('start_date', '>=', request('start_date_from'));
        if (request('start_date_to')) $query->whereDate('start_date', '<=', request('start_date_to'));
        if (request('department_id')) $query->whereHas('user', function ($q) {
            $q->where('department_id', request('department_id'));
        });
        if (request('manager_id')) $query->whereHas('user', function ($q) {
            $q->where('line_manager_id', request('manager_id'));
        });
        $pips = $query->orderByDesc('created_at')->get();
        $csv = "id,user,reason,status,start_date,end_date,created_at\n";
        foreach ($pips as $p) {
            $csv .= implode(',', [
                $p->id,
                '"' . str_replace('"', '""', $p->user->name) . '"',
                '"' . str_replace('"', '""', $p->reason) . '"',
                $p->status,
                $p->start_date,
                $p->end_date,
                $p->created_at->toDateTimeString()
            ]) . "\n";
        }
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pips_export.csv"'
        ]);
    }

    public function close(Request $request, $id)
    {
        $pip = Pip::findOrFail($id);
        $this->authorize('close', $pip);
        $pip->update(['status' => 'closed']);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'pip_closed',
            'table_name' => 'pips',
            'record_id' => $pip->id,
            'details' => "PIP closed: ID {$pip->id}",
        ]);
        return redirect()->route('pips.index')->with('success', 'PIP closed.');
    }

    // helper to send notification (not used directly as route)
    public static function notifyHrAboutPip(Pip $pip)
    {
        $hrEmail = config('mail.from.address');
        $addresses = [];
        $recipientMap = [];
        if ($hrEmail) $addresses[] = $hrEmail;
        // employee
        if ($pip->user && $pip->user->email) $addresses[] = $pip->user->email;
        // manager
        if ($pip->user && $pip->user->lineManager && $pip->user->lineManager->email) $addresses[] = $pip->user->lineManager->email;

        // dedupe
        $addresses = array_values(array_unique(array_filter($addresses)));
        foreach ($addresses as $addr) {
            // figure out recipient type
            $type = 'hr';
            if ($pip->user && $pip->user->email === $addr) $type = 'employee';
            if ($pip->user && $pip->user->lineManager && $pip->user->lineManager->email === $addr) $type = 'manager';
            try {
                // Use Notification class to allow future channel expansion / queueing
                $user = User::where('email', $addr)->first();
                if ($user) {
                    $user->notify(new \App\Notifications\PipCreated($pip, $type));
                } else {
                    Mail::to($addr)->send(new PipCreatedMail($pip, $type));
                }
            } catch (\Exception $e) {
                // ignore individual send failures
            }
        }
    }
}
