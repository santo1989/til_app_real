@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Audit Log Details</h3>
        <table class="table table-bordered w-50">
            <tr>
                <th>ID</th>
                <td>{{ $log->id }}</td>
            </tr>
            <tr>
                <th>User</th>
                <td>{{ $log->user->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Action</th>
                <td>{{ $log->action }}</td>
            </tr>
            <tr>
                <th>Details</th>
                <td>{{ $log->details }}</td>
            </tr>
            <tr>
                <th>Created At</th>
                <td>{{ $log->created_at ? $log->created_at->format('Y-m-d H:i') : '' }}</td>
            </tr>
        </table>
        <a href="{{ route('audit-logs.edit', $log) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
@endsection
