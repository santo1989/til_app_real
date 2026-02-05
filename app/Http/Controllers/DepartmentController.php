<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\AuditLog;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::paginate(25);
        return view('appraisal.hr_admin.departments_index', compact('departments'));
    }

    public function create()
    {
        return view('appraisal.hr_admin.department_create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'head_id' => 'nullable|exists:users,id',
        ]);
        $dept = Department::create($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'department_created',
            'table_name' => 'departments',
            'record_id' => $dept->id,
            'details' => "Department created: {$dept->name} (ID {$dept->id})",
        ]);
        return redirect()->route('departments.index')->with('success', 'Department created successfully');
    }

    public function edit(Department $department)
    {
        return view('appraisal.hr_admin.department_edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'head_id' => 'nullable|exists:users,id',
        ]);
        $department->update($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'department_updated',
            'table_name' => 'departments',
            'record_id' => $department->id,
            'details' => "Department updated: {$department->name} (ID {$department->id})",
        ]);
        return redirect()->route('departments.index')->with('success', 'Department updated successfully');
    }

    public function destroy(Department $department)
    {
        $deptId = $department->id;
        $deptName = $department->name;
        $department->delete();
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'department_deleted',
            'table_name' => 'departments',
            'record_id' => $deptId,
            'details' => "Department deleted: {$deptName} (ID {$deptId})",
        ]);
        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }
}
