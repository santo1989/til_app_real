@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Team Objective Details</h3>

        <table class="table table-bordered w-75">
            <tr>
                <th width="200">ID</th>
                <td>{{ $objective->id }}</td>
            </tr>
            <tr>
                <th>Department</th>
                <td>{{ $objective->department->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Type</th>
                <td><span class="badge bg-info">Team</span></td>
            </tr>
            <tr>
                <th>Description</th>
                <td>{{ $objective->description }}</td>
            </tr>
            <tr>
                <th>Weightage</th>
                <td>{{ $objective->weightage }}%</td>
            </tr>
            <tr>
                <th>Target</th>
                <td>{{ $objective->target }}</td>
            </tr>
            <tr>
                <th>Is SMART?</th>
                <td>
                    @if ($objective->is_smart)
                        <i class="fas fa-check-circle text-success"></i> Yes
                    @else
                        <i class="fas fa-times-circle text-danger"></i> No
                    @endif
                </td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <span class="badge bg-{{ $objective->status == 'set' ? 'success' : 'warning' }}">
                        {{ ucfirst($objective->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Financial Year</th>
                <td>{{ $objective->financial_year }}</td>
            </tr>
            <tr>
                <th>Created By</th>
                <td>{{ $objective->creator->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Created At</th>
                <td>{{ $objective->created_at->format('Y-m-d H:i:s') }}</td>
            </tr>
            <tr>
                <th>Updated At</th>
                <td>{{ $objective->updated_at->format('Y-m-d H:i:s') }}</td>
            </tr>
        </table>

        <x-ui.button variant="warning" href="{{ route('team.objectives.edit', $objective) }}">Edit</x-ui.button>
        <x-ui.button variant="secondary" href="{{ route('team.objectives.index') }}">Back to List</x-ui.button>
    </div>
@endsection
