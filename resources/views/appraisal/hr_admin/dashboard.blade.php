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
                        <div class="btn-group">
                            <a href="{{ route('users.create') }}" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus"></i> Create User
                            </a>
                            <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Import Users (CSV)</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.index') }}">Open Reports</a></li>
                            </ul>
                        </div>
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
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total IDPs</h5>
                    <h3>{{ $stats['total_idps'] ?? '—' }}</h3>
                    <p class="text-muted">Individual Development Plans</p>
                    <a href="{{ route('idps.index') }}" class="btn btn-sm btn-outline-info">Manage IDPs</a>
                </div>
            </div>
        </div>
    </div>
@endsection
