<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Performance Appraisal') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/auto-refresh.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <i class="fas fa-clipboard-check me-2"></i>
                <span>{{ config('app.name', 'TIL Appraisals') }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="appraisalDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-clipboard-list me-1"></i> Appraisal
                            </a>
                            <ul class="dropdown-menu">
                                @can('view', auth()->user())
                                    <li><a class="dropdown-item" href="{{ route('objectives.my') }}"><i
                                                class="fas fa-user-check me-2"></i>My Objectives</a></li>
                                    <li><a class="dropdown-item" href="{{ route('appraisals.midterm') }}"><i
                                                class="fas fa-calendar-check me-2"></i>Midterm</a></li>
                                    @if (auth()->user()->role === 'employee')
                                        <li><a class="dropdown-item" href="{{ route('appraisal.employee.tabs') }}"><i
                                                    class="fas fa-columns me-2"></i>Employee Appraisal (Tabs)</a></li>
                                    @endif
                                @endcan

                                @can('viewMidterm', auth()->user())
                                    <li><a class="dropdown-item" href="{{ route('objectives.team') }}"><i
                                                class="fas fa-users-cog me-2"></i>Team Objectives</a></li>
                                    <li><a class="dropdown-item" href="{{ route('objectives.approvals') }}"><i
                                                class="fas fa-check-double me-2"></i>Approvals</a></li>
                                    <li><a class="dropdown-item"
                                            href="{{ route('idps.index', ['manager_id' => auth()->id()]) }}"><i
                                                class="fas fa-graduation-cap me-2"></i>Team IDPs</a></li>
                                @endcan

                                @can('viewAny', App\Models\Objective::class)
                                    <li><a class="dropdown-item" href="{{ route('objectives.department') }}"><i
                                                class="fas fa-building me-2"></i>Department Objectives</a></li>
                                    <li><a class="dropdown-item" href="{{ route('objectives.board.index') }}"><i
                                                class="fas fa-layer-group me-2"></i>Set Departmental Objectives</a></li>
                                @endcan

                                @can('viewAny', App\Models\Idp::class)
                                    <li><a class="dropdown-item" href="{{ route('idps.index') }}"><i
                                                class="fas fa-graduation-cap me-2"></i>IDPs</a></li>
                                @endcan

                                @can('viewAny', App\Models\Appraisal::class)
                                    <li><a class="dropdown-item" href="{{ route('appraisals.index') }}"><i
                                                class="fas fa-chart-line me-2"></i>Appraisals</a></li>
                                @endcan

                                @can('viewAny', App\Models\AuditLog::class)
                                    <li><a class="dropdown-item" href="{{ route('audit-logs.index') }}"><i
                                                class="fas fa-clipboard-list me-2"></i>Audit Logs</a></li>
                                @endcan
                            </ul>
                        </li>

                        @can('viewAny', App\Models\Department::class)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="hrDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-briefcase me-1"></i> HR
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('users.index') }}"><i
                                                class="fas fa-users me-2"></i>Users</a></li>
                                    <li><a class="dropdown-item" href="{{ route('departments.index') }}"><i
                                                class="fas fa-building me-2"></i>Departments</a></li>
                                    <li><a class="dropdown-item" href="{{ route('financial-years.index') }}"><i
                                                class="fas fa-calendar-alt me-2"></i>Financial Years</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="{{ route('audit-logs.index') }}"><i
                                                class="fas fa-clipboard-list me-2"></i>All Audit Logs</a></li>
                                    <li><a class="dropdown-item" href="{{ route('audit-logs.create') }}"><i
                                                class="fas fa-plus me-2"></i>Create Audit Log</a></li>
                                </ul>
                            </li>
                        @endcan
                    @endauth
                </ul>
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown">
                                @if (auth()->user()->isSuperAdmin())
                                    <i class="fas fa-user-shield text-danger"></i>
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                                {{ auth()->user()->name }}
                                @if (auth()->user()->isSuperAdmin())
                                    <span class="badge bg-danger">Super Admin</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                                        <i class="fas fa-user"></i> Profile
                                    </a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-cog"></i> Settings
                                    </a></li>

                                @if (session()->has('impersonator_id'))
                                    <li>
                                        <form method="POST" action="{{ route('impersonate.stop') }}" class="m-0">
                                            @csrf
                                            <button class="dropdown-item text-warning" type="submit">
                                                <i class="fas fa-user-lock"></i> Stop Impersonation
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                @else
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                @endif

                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item" type="submit">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        @if (session()->has('impersonator_id'))
            @php $impersonator = \App\Models\User::find(session('impersonator_id')); @endphp
            <div class="alert alert-warning d-flex justify-content-between align-items-center">
                <div>
                    <strong>Impersonation Active:</strong>
                    You are currently impersonating <strong>{{ auth()->user()->name }}</strong>.
                    @if ($impersonator)
                        <small class="text-muted">(original: {{ $impersonator->name }})</small>
                    @endif
                </div>
                <div>
                    <form method="POST" action="{{ route('impersonate.stop') }}" class="m-0">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger">Stop Impersonation</button>
                    </form>
                </div>
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @yield('content')
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function() {
            $('.datatable').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().destroy();
                }
                $(this).DataTable({
                    responsive: true,
                    pageLength: 25
                });
            });
        });
    </script>
    <!-- Auto Refresh Module -->
    <script src="{{ asset('js/auto-refresh.js') }}"></script>
    <script>
        // Enable Bootstrap tooltips for better affordance
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Confirm impersonation actions to avoid accidental switches
        $(function() {
            $('.impersonate-form').on('submit', function(e) {
                var user = $(this).data('user') || 'the user';
                if (!confirm('Start impersonating ' + user +
                        '? You can stop impersonation via your profile menu.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>

<!-- ...existing code... -->
