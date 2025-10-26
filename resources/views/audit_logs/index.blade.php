@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Audit Logs</h3>
            <a href="{{ route('audit-logs.create') }}" class="btn btn-primary">Add Audit Log</a>
        </div>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->user->name ?? 'N/A' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ Str::limit($log->details, 50) }}</td>
                            <td>{{ $log->created_at ? $log->created_at->format('Y-m-d H:i') : '' }}</td>
                            <td>
                                <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('audit-logs.edit', $log) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('audit-logs.destroy', $log) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Delete this log?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
