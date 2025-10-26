<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

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
        Department::create($data);
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
        return redirect()->route('departments.index')->with('success', 'Department updated successfully');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }
}
