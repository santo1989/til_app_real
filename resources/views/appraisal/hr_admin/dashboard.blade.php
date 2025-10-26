@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">HR Admin Dashboard</h4>
                        <p class="text-muted mb-0">Welcome, {{ auth()->user()->name }} — quick actions and reports.</p>
                    </div>
                    <div>
                        <a href="{{ route('users.create') }}" class="btn btn-primary">Create User</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Users</h5>
                    <h3>{{ $stats['total_users'] ?? '—' }}</h3>
                    <p class="text-muted">Active: {{ $stats['active_users'] ?? '—' }}</p>
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">Manage Users</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Departments</h5>
                    <h3>{{ $stats['total_departments'] ?? '—' }}</h3>
                    <a href="{{ route('departments.index') }}" class="btn btn-sm btn-outline-info">Manage Departments</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Reports</h5>
                    <p class="text-muted">Generate standard HR reports</p>
                    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-success">Open Reports</a>
                </div>
            </div>
        </div>
    </div>
@endsection
