@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Edit Department: {{ $department->name }}</h5>
            <form method="POST" action="{{ route('departments.update', $department) }}">
                @csrf
                @method('PUT')
                @include('components.alert')

                <div class="mb-3">
                    <label for="name" class="form-label">Department Name</label>
                    <input type="text" name="name" id="name" class="form-control"
                        value="{{ old('name', $department->name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="head_id" class="form-label">Department Head</label>
                    <select name="head_id" id="head_id" class="form-control">
                        <option value="">-- Select Head --</option>
                        @foreach (App\Models\User::whereIn('role', ['dept_head', 'hr_admin', 'line_manager', 'board'])->get() as $user)
                            <option value="{{ $user->id }}"
                                {{ old('head_id', $department->head_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <x-ui.button variant="primary" type="submit">Update Department</x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('departments.index') }}">Cancel</x-ui.button>
                <x-ui.button variant="danger" type="button" class="float-end"
                    onclick="if(confirm('Delete this department?')) document.getElementById('delete-form').submit()">Delete</x-ui.button>
            </form>

            <form id="delete-form" method="POST" action="{{ route('departments.destroy', $department) }}" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
@endsection
