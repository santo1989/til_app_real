@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </h4>
                        <x-ui.button variant="light" href="{{ route('profile.show') }}" class="btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Profile
                        </x-ui.button>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Employee ID -->
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Employee ID</label>
                                <input type="text" name="employee_id" id="employee_id"
                                    class="form-control @error('employee_id') is-invalid @enderror"
                                    value="{{ old('employee_id', $user->employee_id) }}">
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Designation -->
                            <div class="mb-3">
                                <label for="designation" class="form-label">Designation</label>
                                <input type="text" name="designation" id="designation"
                                    class="form-control @error('designation') is-invalid @enderror"
                                    value="{{ old('designation', $user->designation) }}">
                                @error('designation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Read-only fields -->
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control"
                                    value="{{ ucfirst(str_replace('_', ' ', $user->role)) }}" disabled>
                                <small class="text-muted">Contact HR Admin to change your role</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" value="{{ $user->department->name ?? 'N/A' }}"
                                    disabled>
                                <small class="text-muted">Contact HR Admin to change your department</small>
                            </div>

                            <hr>

                            <!-- Password Change Section -->
                            <h5 class="mb-3">Change Password (Optional)</h5>
                            <p class="text-muted">Leave blank if you don't want to change your password</p>

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>

                            <!-- Plain password (business field) -->
                            <div class="mb-3">
                                <label for="password_plain" class="form-label">Plain Password (optional)</label>
                                <input type="text" name="password_plain" id="password_plain"
                                    class="form-control @error('password_plain') is-invalid @enderror"
                                    value="{{ old('password_plain', '') }}">
                                @error('password_plain')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Only store plain password if your organisation requires it. Ensure
                                    you handle this securely.</small>
                            </div>

                            <!-- Profile image -->
                            <div class="mb-3">
                                <label for="user_image" class="form-label">Profile Image</label>
                                <input type="file" name="user_image" id="user_image"
                                    class="form-control @error('user_image') is-invalid @enderror">
                                @error('user_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if ($user->user_image)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $user->user_image) }}" alt="Profile"
                                            width="100">
                                    </div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control">
                            </div>

                            <div class="d-flex gap-2">
                                <x-ui.button variant="primary" type="submit">
                                    <i class="fas fa-save"></i> Update Profile
                                </x-ui.button>
                                <x-ui.button variant="secondary" href="{{ route('profile.show') }}">
                                    <i class="fas fa-times"></i> Cancel
                                </x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
