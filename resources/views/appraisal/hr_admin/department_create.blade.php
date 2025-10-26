@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Create Department</h5>
            <form method="POST" action="{{ route('departments.store') }}">
                @csrf
                @include('components.alert')
                <div class="mb-3">
                    <label for="name" class="form-label">Department Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="head_id" class="form-label">Department Head</label>
                    <select name="head_id" id="head_id" class="form-control">
                        <option value="">-- Select Head --</option>
                        @foreach (App\Models\User::whereIn('role', ['dept_head', 'hr_admin', 'line_manager', 'board'])->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>
@endsection
