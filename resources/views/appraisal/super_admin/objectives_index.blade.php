@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-bullseye"></i> All Objectives</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Weight</th>
                            <th>Status</th>
                            <th>FY</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($objectives as $o)
                            <tr>
                                <td>{{ $o->id }}</td>
                                <td>{{ $o->user->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($o->type) }}</span></td>
                                <td>{{ Str::limit($o->description, 60) }}</td>
                                <td>{{ $o->weightage }}%</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $o->status == 'set' ? 'success' : ($o->status == 'draft' ? 'warning' : 'info') }}">
                                        {{ ucfirst($o->status) }}
                                    </span>
                                </td>
                                <td>{{ $o->financial_year }}</td>
                                <td>{{ $o->creator->name ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
