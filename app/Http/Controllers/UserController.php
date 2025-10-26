<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;

class UserController extends Controller
{
    /**
     * Super Admin only: Show all users with disguised password column
     */
    public function superAdminUserIndex()
    {
        // Only allow super admin
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }
        $users = User::with('department')->get();
        return view('appraisal.super_admin.users_index', compact('users'));
    }
    /**
     * Show the user's profile
     */
    public function profile()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->load(['department', 'lineManager', 'objectives', 'appraisals', 'idps']);
        return view('profile.show', compact('user'));
    }

    /**
     * Show the form to edit user's profile
     */
    public function editProfile()
    {
        $user = auth()->user();
        $departments = Department::all();
        return view('profile.edit', compact('user', 'departments'));
    }

    /**
     * Update the user's profile
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'employee_id' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'password_plain' => 'nullable|string|max:255',
            'user_image' => 'nullable|image|max:2048',
        ]);
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        // handle uploaded profile image
        if ($request->hasFile('user_image')) {
            $path = $request->file('user_image')->store('user_images', 'public');
            $data['user_image'] = $path;
        }
        // allow saving plain password when provided (business requirement)
        if (empty($data['password_plain'])) {
            unset($data['password_plain']);
        }
        $user->fill($data);
        $user->save();
        return redirect()->route('profile.show')->with('success', 'Profile updated successfully');
    }

    public function index(Request $request)
    {
        $users = User::with('department')->get();
        return view('appraisal.hr_admin.users_index', compact('users'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('appraisal.hr_admin.user_create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_plain' => 'nullable|string|max:255',
            'user_image' => 'nullable|image|max:2048',
            'role' => 'required|string|in:employee,line_manager,dept_head,board,hr_admin,super_admin',
            'department_id' => 'nullable|exists:departments,id',
            'line_manager_id' => 'nullable|exists:users,id',
        ]);

        $data['password'] = bcrypt($data['password']);
        // store uploaded image if provided
        if ($request->hasFile('user_image')) {
            $data['user_image'] = $request->file('user_image')->store('user_images', 'public');
        }
        User::create($data);

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    public function edit(User $user)
    {
        $departments = Department::all();
        return view('appraisal.hr_admin.user_edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:50',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'password_plain' => 'nullable|string|max:255',
            'user_image' => 'nullable|image|max:2048',
            'role' => 'required|string|in:employee,line_manager,dept_head,board,hr_admin,super_admin',
            'department_id' => 'nullable|exists:departments,id',
            'line_manager_id' => 'nullable|exists:users,id',
        ]);
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        // handle uploaded profile image for admin editing
        if ($request->hasFile('user_image')) {
            $data['user_image'] = $request->file('user_image')->store('user_images', 'public');
        }
        if (empty($data['password_plain'])) {
            unset($data['password_plain']);
        }
        /** @var \App\Models\User $user */
        $user->fill($data);
        $user->save();
        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    /**
     * Show a user's public profile (for HR / super admin / manager or self)
     */
    public function show(User $user)
    {
        // Use the centralized UserPolicy to determine access. This keeps authorization
        // logic in one place and avoids duplicated manual checks.
        $this->authorize('view', $user);

        $user->load(['department', 'lineManager', 'objectives', 'appraisals', 'idps']);
        return view('profile.show', compact('user'));
    }
}
