@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Create User</h5>
            <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                @csrf
                @include('components.alert')

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}"
                        required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}"
                        required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password_plain" class="form-label">Plain Password (optional)</label>
                    <input type="text" name="password_plain" id="password_plain" class="form-control"
                        value="{{ old('password_plain') }}">
                    <small class="text-muted">If you need to record a plain password for onboarding, enter it here.</small>
                </div>

                <div class="mb-3">
                    <label for="user_image" class="form-label">Profile Image</label>
                    <input type="file" name="user_image" id="user_image" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                        required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-control" required>
                        <option value="">-- Select Role --</option>
                        <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                        <option value="line_manager" {{ old('role') == 'line_manager' ? 'selected' : '' }}>Line Manager
                        </option>
                        <option value="dept_head" {{ old('role') == 'dept_head' ? 'selected' : '' }}>Department Head
                        </option>
                        <option value="board" {{ old('role') == 'board' ? 'selected' : '' }}>Board Member</option>
                        <option value="hr_admin" {{ old('role') == 'hr_admin' ? 'selected' : '' }}>HR Admin</option>
                        <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="department_id" class="form-label">Department</label>
                    <select name="department_id" id="department_id" class="form-control">
                        <option value="">-- Select Department --</option>
                        @foreach (App\Models\Department::all() as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="line_manager_id" class="form-label">Line Manager</label>
                    <select name="line_manager_id" id="line_manager_id" class="form-control">
                        <option value="">-- Select Line Manager --</option>
                        @foreach (App\Models\User::whereIn('role', ['line_manager', 'dept_head', 'hr_admin'])->get() as $mgr)
                            <option value="{{ $mgr->id }}"
                                {{ old('line_manager_id') == $mgr->id ? 'selected' : '' }}>
                                {{ $mgr->name }} ({{ $mgr->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
