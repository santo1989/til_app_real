@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Profile Header Card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-user-circle"></i> My Profile
                        </h4>
                        <div>
                            <x-ui.button variant="light" href="{{ route('profile.edit') }}" class="btn-sm">
                                <i class="fas fa-edit"></i> Edit Profile
                            </x-ui.button>
                            @can('view', $user)
                                <x-ui.button variant="primary" href="{{ route('idps.index', ['user_id' => $user->id]) }}"
                                    class="btn-sm ms-2">
                                    <i class="fas fa-graduation-cap"></i> View IDPs
                                </x-ui.button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center border-end">
                                <div class="mb-3">
                                    @if ($user->user_image)
                                        <img src="{{ asset('storage/' . $user->user_image) }}" alt="Profile"
                                            class="rounded-circle" width="120" height="120">
                                    @else
                                        <i class="fas fa-user-circle fa-6x text-primary"></i>
                                    @endif
                                </div>
                                <h5>{{ $user->name }}</h5>
                                <p class="text-muted">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
                                @if ($user->isSuperAdmin())
                                    <span class="badge bg-danger">Super Admin</span>
                                @elseif($user->isHrAdmin())
                                    <span class="badge bg-info">HR Admin</span>
                                @elseif($user->isBoardMember())
                                    <span class="badge bg-warning">Board Member</span>
                                @elseif($user->isDeptHead())
                                    <span class="badge bg-success">Department Head</span>
                                @elseif($user->isLineManager())
                                    <span class="badge bg-primary">Line Manager</span>
                                @else
                                    <span class="badge bg-secondary">Employee</span>
                                @endif
                            </div>
                            <div class="col-md-9">
                                <h5 class="mb-3">Personal Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="200"><i class="fas fa-id-badge text-primary"></i> Employee ID:</th>
                                        <td>{{ $user->employee_id ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-envelope text-primary"></i> Email:</th>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-briefcase text-primary"></i> Designation:</th>
                                        <td>{{ $user->designation ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-building text-primary"></i> Department:</th>
                                        <td>{{ $user->department->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-user-tie text-primary"></i> Line Manager:</th>
                                        <td>{{ $user->lineManager->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-calendar-alt text-primary"></i> Date of Joining:</th>
                                        <td>{{ $user->date_of_joining ? \Carbon\Carbon::parse($user->date_of_joining)->format('d M, Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-clock text-primary"></i> Tenure in Current Role:</th>
                                        <td>{{ $user->tenure_in_current_role ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-toggle-on text-primary"></i> Status:</th>
                                        <td>
                                            @if ($user->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center border-warning">
                            <div class="card-body">
                                <i class="fas fa-bullseye fa-3x text-warning mb-2"></i>
                                <h3 class="mb-0">{{ $user->objectives->count() }}</h3>
                                <p class="text-muted mb-0">Objectives</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center border-success">
                            <div class="card-body">
                                <i class="fas fa-chart-line fa-3x text-success mb-2"></i>
                                <h3 class="mb-0">{{ $user->appraisals->count() }}</h3>
                                <p class="text-muted mb-0">Appraisals</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center border-info">
                            <div class="card-body">
                                <i class="fas fa-graduation-cap fa-3x text-info mb-2"></i>
                                <h3 class="mb-0">{{ $user->idps->count() }}</h3>
                                <p class="text-muted mb-0">IDPs</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <strong>Quick Actions</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('objectives.my') }}" class="btn btn-outline-primary">My Objectives</a>
                            <a href="{{ route('appraisal.employee.tabs') }}" class="btn btn-outline-secondary">Appraisal
                                (Tabbed View)</a>
                            <a href="{{ route('idp.index') }}" class="btn btn-outline-info">My IDP</a>
                            @if ($user->isLineManager())
                                <a href="{{ route('objectives.team') }}" class="btn btn-outline-success">Team
                                    Objectives</a>
                                <a href="{{ route('idps.index', ['manager_id' => $user->id]) }}"
                                    class="btn btn-outline-warning">Team IDPs</a>
                            @endif
                            @if ($user->isHrAdmin() || $user->isSuperAdmin())
                                <a href="{{ route('objectives.index') }}" class="btn btn-outline-dark">All Objectives</a>
                                <a href="{{ route('idps.index') }}" class="btn btn-outline-dark">All IDPs</a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Objectives -->
                @if ($user->objectives->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-bullseye"></i> Recent Objectives</h5>
                            <div>
                                <a href="{{ route('users.objectives.pdf', ['user_id' => $user->id]) }}" target="_blank"
                                    class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-file-pdf"></i> Download PDF
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Type</th>
                                            <th>Weightage</th>
                                            <th>Status</th>
                                            <th>Financial Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($user->objectives->take(5) as $objective)
                                            <tr>
                                                <td>{{ Str::limit($objective->description, 50) }}</td>
                                                <td><span class="badge bg-secondary">{{ $objective->type }}</span></td>
                                                <td>{{ $objective->weightage }}%</td>
                                                <td>
                                                    @php
                                                        $statusClass = match ($objective->status) {
                                                            'set' => 'bg-success',
                                                            'draft' => 'bg-warning',
                                                            'submitted' => 'bg-info',
                                                            default => 'bg-secondary',
                                                        };
                                                    @endphp
                                                    <span
                                                        class="badge {{ $statusClass }}">{{ ucfirst($objective->status) }}</span>
                                                </td>
                                                <td>{{ $objective->financial_year }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Recent Appraisals -->
                @if ($user->appraisals->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-chart-line"></i> Recent Appraisals</h5>
                            <div>
                                {{-- Download latest year-end PDF if available --}}
                                @php $latest = $user->appraisals->first(); @endphp
                                @if ($latest)
                                    <a href="{{ route('appraisals.yearend.pdf', ['appraisal_id' => $latest->id]) }}"
                                        target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-file-pdf"></i> Download Latest PDF
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Achievement Score</th>
                                            <th>Total Score</th>
                                            <th>Rating</th>
                                            <th>Financial Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($user->appraisals->take(5) as $appraisal)
                                            <tr>
                                                <td><span class="badge bg-info">{{ ucfirst($appraisal->type) }}</span>
                                                </td>
                                                <td>{{ $appraisal->date ? \Carbon\Carbon::parse($appraisal->date)->format('d M, Y') : 'N/A' }}
                                                </td>
                                                <td>{{ $appraisal->achievement_score ?? 'N/A' }}</td>
                                                <td>{{ $appraisal->total_score ?? 'N/A' }}</td>
                                                <td>
                                                    @if ($appraisal->rating)
                                                        @php
                                                        @endphp
                                                        @php
                                                            // map lowercased rating to badge classes
                                                            $ratingClass = match (strtolower($appraisal->rating)) {
                                                                'outstanding' => 'bg-success',
                                                                'excellent' => 'bg-success',
                                                                'good' => 'bg-primary',
                                                                'average' => 'bg-warning',
                                                                'below average' => 'bg-danger',
                                                                default => 'bg-secondary',
                                                            };
                                                        @endphp
                                                        <span
                                                            class="badge {{ $ratingClass }}">{{ $appraisal->rating }}</span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>{{ $appraisal->financial_year }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Recent IDPs -->
                @if ($user->idps->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Recent IDPs</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Review Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($user->idps->take(5) as $idp)
                                            <tr>
                                                <td>{{ Str::limit($idp->description, 60) }}</td>
                                                <td>{{ $idp->review_date ? \Carbon\Carbon::parse($idp->review_date)->format('d M, Y') : 'N/A' }}
                                                </td>
                                                <td>
                                                    @if ($idp->status)
                                                        @php
                                                            $statusClass = match ($idp->status) {
                                                                'completed' => 'bg-success',
                                                                'in_progress' => 'bg-primary',
                                                                'pending' => 'bg-warning',
                                                                default => 'bg-secondary',
                                                            };
                                                        @endphp
                                                        <span
                                                            class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $idp->status)) }}</span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
