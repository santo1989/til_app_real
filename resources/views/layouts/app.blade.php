<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Performance Appraisal') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">TIL Appraisals</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>

                        @if (auth()->user()->role === 'super_admin')
                            <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">
                                    <i class="fas fa-users"></i> Users
                                </a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('departments.index') }}">
                                    <i class="fas fa-building"></i> Departments
                                </a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('objectives.index') }}">
                                    <i class="fas fa-bullseye"></i> Objectives
                                </a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('appraisals.index') }}">
                                    <i class="fas fa-chart-line"></i> Appraisals
                                </a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('idps.index') }}">
                                    <i class="fas fa-graduation-cap"></i> IDPs
                                </a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('audit-logs.index') }}">
                                    <i class="fas fa-clipboard-list"></i> Audit Logs
                                </a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('financial-years.index') }}">
                                    <i class="fas fa-calendar-alt"></i> Financial Years
                                </a>
                            </li>
                        @endif

                        @if (in_array(auth()->user()->role, ['employee', 'super_admin']))
                            <li class="nav-item"><a class="nav-link" href="{{ route('objectives.my') }}">My Objectives</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('appraisals.midterm') }}">Midterm</a>
                            </li>
                        @endif

                        @if (in_array(auth()->user()->role, ['line_manager', 'super_admin']))
                            <li class="nav-item"><a class="nav-link" href="{{ route('objectives.team') }}">Team
                                    Objectives</a></li>
                            <li class="nav-item"><a class="nav-link" href="#">Conduct Reviews</a></li>
                        @endif

                        @if (in_array(auth()->user()->role, ['dept_head', 'super_admin']))
                            <li class="nav-item"><a class="nav-link"
                                    href="{{ route('objectives.department') }}">Department
                                    Objectives</a></li>
                        @endif

                        @if (in_array(auth()->user()->role, ['board', 'super_admin']))
                            <li class="nav-item"><a class="nav-link" href="{{ route('objectives.board.index') }}">Set
                                    Departmental Objectives</a></li>
                        @endif

                        @if (auth()->user()->role === 'hr_admin')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="auditLogDropdown" role="button"
                                    data-bs-toggle="dropdown">
                                    <i class="fas fa-clipboard-list"></i> Audit Logs
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('audit-logs.index') }}">All Audit
                                            Logs</a></li>
                                    <li><a class="dropdown-item" href="{{ route('audit-logs.create') }}">Create Audit
                                            Log</a></li>
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Users</a></li>
                        @endif

            @if (auth()->user()->role === 'hr_admin')
                <li class="nav-item"><a class="nav-link"
                    href="{{ route('departments.index') }}">Departments</a>
                </li>
                <li class="nav-item"><a class="nav-link"
                    href="{{ route('financial-years.index') }}">Financial Years</a></li>
            @endif
                    @endauth
                </ul>
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown">
                                @if (auth()->user()->role === 'super_admin')
                                    <i class="fas fa-user-shield text-danger"></i>
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                                {{ auth()->user()->name }}
                                @if (auth()->user()->role === 'super_admin')
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
                        <button class="btn btn-sm btn-danger">Stop Impersonation</button>
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
</body>

</html>

<!-- ...existing code... -->
