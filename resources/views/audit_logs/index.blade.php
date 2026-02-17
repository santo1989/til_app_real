@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card card-responsive">
            <div class="card-header bg-info text-white d-flex flex-wrap justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Audit Logs</h5>
                <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                    <span class="auto-refresh-badge">Auto-refresh: 30s</span>
                    <button class="btn btn-sm btn-light" data-manual-refresh="audit-logs-table-container"
                        data-refresh-url="{{ route('audit-logs.index') }}">
                        <i class="fas fa-sync-alt"></i> Refresh Now
                    </button>
                    <x-ui.button variant="primary" href="{{ route('audit-logs.create') }}" class="btn-sm">Add Audit
                        Log</x-ui.button>
                </div>
            </div>
            <div class="card-body" id="audit-logs-table-container" data-auto-refresh="true"
                data-refresh-url="{{ route('audit-logs.index') }}" data-refresh-target="audit-logs-table-container">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="table-responsive-custom">
                    <table class="table table-bordered table-hover datatable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Action</th>
                                <th class="hide-mobile">Details</th>
                                <th class="hide-mobile">Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td class="text-truncate-mobile">{{ $log->user->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-info badge-responsive">{{ $log->action }}</span></td>
                                    <td class="hide-mobile">{{ Str::limit($log->details, 50) }}</td>
                                    <td class="hide-mobile">
                                        {{ $log->created_at ? $log->created_at->format('Y-m-d H:i') : '' }}</td>
                                    <td>
                                        <div class="btn-group-mobile">
                                            <x-ui.button variant="info" href="{{ route('audit-logs.show', $log) }}"
                                                class="btn-sm">View</x-ui.button>
                                            <x-ui.button variant="warning" href="{{ route('audit-logs.edit', $log) }}"
                                                class="btn-sm">Edit</x-ui.button>
                                            <form action="{{ route('audit-logs.destroy', $log) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Delete this log?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button variant="danger" type="submit"
                                                    class="btn-sm">Delete</x-ui.button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
