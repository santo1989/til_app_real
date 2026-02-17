@extends('layouts.app')
@section('content')
    <div class="card card-responsive">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0"><i class="fas fa-building"></i> Departments</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-sync-alt"></i> Auto-refresh: 30s
                </span>
                <button class="btn btn-sm btn-outline-light" onclick="AutoRefresh.manualRefresh('departments-container')">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body" id="departments-container" data-auto-refresh="true"
            data-refresh-url="{{ route('hr_admin.departments_index') }}" data-refresh-target="#departments-container">
            <x-ui.button variant="primary" href="{{ route('departments.create') }}" class="mb-2">Create
                Department</x-ui.button>
            <div class="table-responsive-custom">
                <table class="table datatable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Head</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $i => $d)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="text-truncate-mobile">{{ $d->name }}</td>
                                <td class="hide-mobile">{{ $d->head ? $d->head->name : '-' }}</td>
                                <td><x-ui.button variant="secondary" href="{{ route('departments.edit', $d) }}"
                                        class="btn-sm">Edit</x-ui.button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
