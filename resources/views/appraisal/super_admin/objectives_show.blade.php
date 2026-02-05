@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Objective Details #{{ $objective->id }}</h4>
                        <div>
                            <x-ui.button variant="warning" href="{{ route('objectives.edit', $objective) }}"
                                class="btn-sm">Edit</x-ui.button>
                            <x-ui.button variant="secondary" href="{{ route('objectives.index') }}" class="btn-sm">Back to
                                List</x-ui.button>
                            <form action="{{ route('objectives.destroy', $objective) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Are you sure you want to delete this objective?');">
                                @csrf
                                @method('DELETE')
                                <x-ui.button variant="danger" type="submit" class="btn-sm">Delete</x-ui.button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">ID</th>
                                <td>{{ $objective->id }}</td>
                            </tr>
                            <tr>
                                <th>User</th>
                                <td>{{ $objective->user->name ?? 'N/A' }} ({{ $objective->user->email ?? 'N/A' }})</td>
                            </tr>
                            <tr>
                                <th>Department</th>
                                <td>{{ $objective->department->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Type</th>
                                <td><span class="badge bg-info">{{ ucfirst($objective->type) }}</span></td>
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
                                <th>Financial Year</th>
                                <td>{{ $objective->financial_year }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="badge bg-success">{{ ucfirst($objective->status ?? 'N/A') }}</span></td>
                            </tr>
                            <tr>
                                <th>SMART Objective</th>
                                <td>
                                    @if ($objective->is_smart)
                                        <i class="fas fa-check-circle text-success"></i> Yes
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i> No
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created By</th>
                                <td>{{ $objective->creator->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ optional($objective->created_at)->format('Y-m-d H:i:s') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ optional($objective->updated_at)->format('Y-m-d H:i:s') ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
