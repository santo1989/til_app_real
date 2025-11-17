@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient text-white"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body">
                        <h3 class="mb-0">
                            <i class="fas fa-user-shield"></i> Super Admin Dashboard
                        </h3>
                        <p class="mb-0 mt-2">Welcome back, {{ auth()->user()->name }}! You have full system access.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-6 col-md-3 mb-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-users stat-icon text-primary mb-3"></i>
                        <h4 class="mb-1">{{ $stats['total_users'] }}</h4>
                        <p class="text-muted mb-0">Total Users</p>
                        <small class="text-success">{{ $stats['active_users'] }} Active</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-building stat-icon text-info mb-3"></i>
                        <h4 class="mb-1">{{ $stats['total_departments'] }}</h4>
                        <p class="text-muted mb-0">Departments</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-bullseye stat-icon text-warning mb-3"></i>
                        <h4 class="mb-1">{{ $stats['total_objectives'] }}</h4>
                        <p class="text-muted mb-0">Total Objectives</p>
                        <small class="text-warning">{{ $stats['pending_objectives'] }} Pending</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line stat-icon text-success mb-3"></i>
                        <h4 class="mb-1">{{ $stats['total_appraisals'] }}</h4>
                        <p class="text-muted mb-0">Total Appraisals</p>
                        <small class="text-success">{{ $stats['completed_appraisals'] }} Completed</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- Note: Quick Access has been removed from dashboard; use the nav bar to access modules -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    Use the navigation bar to access system modules. This dashboard shows summary information only.
                </div>
            </div>
        </div>

        <!-- Three Column Layout -->
        <div class="row">
            <!-- Recent Users -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-user-clock"></i> Recent Users</h6>
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentUsers as $user)
                                <div class="list-group-item">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $user->name }}</h6>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                        <div class="d-flex align-items-center mt-2 mt-md-0">
                                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} me-2">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                            <a href="{{ route('users.edit', $user) }}"
                                                class="btn btn-sm btn-light">Edit</a>
                                            @if (auth()->user()->role === 'super_admin' && $user->role !== 'super_admin')
                                                <form method="POST" action="{{ route('impersonate.start', $user) }}"
                                                    class="ms-2 m-0 p-0 impersonate-form" data-user="{{ $user->name }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-primary" title="Act as this user">Impersonate</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-center text-muted">
                                    <i class="fas fa-inbox"></i> No users found
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Objectives -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-bullseye"></i> Recent Objectives</h6>
                        <a href="{{ route('objectives.index') }}" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentObjectives as $objective)
                                <a href="{{ route('objectives.show', $objective) }}"
                                    class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ Str::limit($objective->description, 30) }}</h6>
                                            <small class="text-muted">{{ $objective->user->name ?? 'N/A' }}</small>
                                        </div>
                                        <span
                                            class="badge bg-{{ $objective->status == 'set' ? 'success' : ($objective->status == 'draft' ? 'warning' : 'info') }}">
                                            {{ ucfirst($objective->status) }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div class="list-group-item text-center text-muted">
                                    <i class="fas fa-inbox"></i> No objectives found
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Appraisals -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-chart-line"></i> Recent Appraisals</h6>
                        <a href="{{ route('appraisals.index') }}" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentAppraisals as $appraisal)
                                <a href="{{ route('appraisals.show', $appraisal) }}"
                                    class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $appraisal->user->name ?? 'N/A' }}</h6>
                                            <small
                                                class="text-muted">{{ $appraisal->type ?? ($appraisal->appraisal_type ?? 'N/A') }}</small>
                                        </div>
                                        <span
                                            class="badge bg-{{ $appraisal->status == 'completed' ? 'success' : 'info' }}">
                                            {{ ucfirst($appraisal->status) }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div class="list-group-item text-center text-muted">
                                    <i class="fas fa-inbox"></i> No appraisals found
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departments Overview -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-building"></i> Departments Overview</h5>
                        <a href="{{ route('departments.index') }}" class="btn btn-sm btn-light">Manage Departments</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Department Name</th>
                                        <th>Department Code</th>
                                        <th>Total Employees</th>
                                        <th>Department Head</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($departments as $department)
                                        <tr>
                                            <td>
                                                <i class="fas fa-building text-info"></i>
                                                <strong>{{ $department->name }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $department->code }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $department->users_count ?? 0 }}
                                                    Employees</span>
                                            </td>
                                            <td>
                                                @if ($department->head)
                                                    {{ $department->head->name }}
                                                @else
                                                    <span class="text-muted">Not Assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('departments.edit', $department) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No departments found. <a
                                                        href="{{ route('departments.create') }}">Create one now</a></p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> System Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Performance Summary</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check-circle text-success"></i> Active Users:
                                        <strong>{{ $stats['active_users'] }}</strong>
                                    </li>
                                    <li><i class="fas fa-clock text-warning"></i> Pending Objectives:
                                        <strong>{{ $stats['pending_objectives'] }}</strong>
                                    </li>
                                    <li><i class="fas fa-check-circle text-success"></i> Approved Objectives:
                                        <strong>{{ $stats['approved_objectives'] }}</strong>
                                    </li>
                                    <li><i class="fas fa-hourglass-half text-info"></i> Pending Appraisals:
                                        <strong>{{ $stats['pending_appraisals'] }}</strong>
                                    </li>
                                    <li><i class="fas fa-check-circle text-success"></i> Completed Appraisals:
                                        <strong>{{ $stats['completed_appraisals'] }}</strong>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Quick Actions</h6>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                                        <i class="fas fa-print"></i> Print Dashboard
                                    </button>
                                    <a href="#" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-file-excel"></i> Export to Excel
                                    </a>
                                    <a href="#" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-file-pdf"></i> Generate PDF Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-icon {
            font-size: 3rem;
        }

        @media (max-width: 576px) {
            .stat-icon {
                font-size: 1.6rem;
            }
        }
    </style>
@endsection
