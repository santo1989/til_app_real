@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Manage Team/Department Objectives</h3>
            <x-ui.button variant="primary" href="{{ route('team.objectives.create') }}">Add Team Objective</x-ui.button>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered datatable" id="DataTables_Table_0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Department</th>
                        <th>Weightage %</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>FY</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teamObjectives as $obj)
                        <tr>
                            <td>{{ $obj->id }}</td>
                            <td>{{ Str::limit($obj->description, 60) }}</td>
                            <td>{{ $obj->department ? $obj->department->name : '-' }}</td>
                            <td>{{ $obj->weightage }}%</td>
                            <td>{{ Str::limit($obj->target, 40) }}</td>
                            <td>
                                <span class="badge bg-{{ $obj->status == 'set' ? 'success' : 'warning' }}">
                                    {{ ucfirst($obj->status) }}
                                </span>
                            </td>
                            <td>{{ $obj->financial_year }}</td>
                            <td>
                                <x-ui.button variant="info" href="{{ route('team.objectives.show', $obj) }}"
                                    class="btn-sm">View</x-ui.button>
                                <x-ui.button variant="warning" href="{{ route('team.objectives.edit', $obj) }}"
                                    class="btn-sm">Edit</x-ui.button>
                                <form action="{{ route('team.objectives.destroy', $obj) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Delete this team objective?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="danger" type="submit" class="btn-sm">Delete</x-ui.button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No team objectives found. Create one now!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
