@extends('layouts.app')
@section('content')
    <div class="card card-responsive">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Reports</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-sync-alt"></i> Auto-refresh: 30s
                </span>
                <button class="btn btn-sm btn-outline-light" data-manual-refresh="reports-container"
                    data-refresh-url="{{ route('reports.index') }}">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body" id="reports-container" data-auto-refresh="true"
            data-refresh-url="{{ route('reports.index') }}" data-refresh-target="reports-container">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-muted">Total Appraisals</div>
                            <div class="h4 mb-0">{{ $totalAppraisals ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-muted">Average Score</div>
                            <div class="h4 mb-0">{{ number_format((float) ($avgScore ?? 0), 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-stretch">
                    <div class="card w-100">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <div class="text-muted mb-2">Exports</div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a class="btn btn-outline-primary btn-sm" href="#">Export Appraisals (PDF)</a>
                                <a class="btn btn-outline-secondary btn-sm" href="#">Export Excel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                        placeholder="Search name or email">
                </div>
                <div class="col-md-2">
                    <select name="fy" class="form-control">
                        <option value="">All FYs</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" {{ request('fy') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="midterm" {{ request('type') == 'midterm' ? 'selected' : '' }}>Midterm</option>
                        <option value="year_end" {{ request('type') == 'year_end' ? 'selected' : '' }}>Year End</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                        </option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="rating" class="form-control">
                        <option value="">All Ratings</option>
                        <option value="outstanding" {{ request('rating') == 'outstanding' ? 'selected' : '' }}>Outstanding
                        </option>
                        <option value="good" {{ request('rating') == 'good' ? 'selected' : '' }}>Good</option>
                        <option value="average" {{ request('rating') == 'average' ? 'selected' : '' }}>Average</option>
                        <option value="below" {{ request('rating') == 'below' ? 'selected' : '' }}>Below</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </form>

            <div class="table-responsive-custom">
                <table class="table table-striped table-hover datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="hide-mobile">Score</th>
                            <th class="hide-mobile">Rating</th>
                            <th class="hide-mobile">FY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appraisals as $a)
                            @php
                                $displayRating = $a->rating ? \App\Support\Rating::toDisplayLabel($a->rating) : '-';
                            @endphp
                            <tr>
                                <td>{{ $a->id }}</td>
                                <td class="text-truncate-mobile">{{ $a->user->name ?? 'N/A' }}</td>
                                <td><span
                                        class="badge bg-secondary badge-responsive">{{ str_replace('_', ' ', ucfirst($a->type)) }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge badge-responsive bg-{{ $a->status === 'completed' ? 'success' : ($a->status === 'pending' ? 'warning' : 'info') }}">{{ ucfirst($a->status ?? 'n/a') }}</span>
                                </td>
                                <td class="hide-mobile">{{ $a->total_score ?? ($a->achievement_score ?? '-') }}</td>
                                <td class="hide-mobile">{{ $displayRating }}</td>
                                <td class="hide-mobile">{{ $a->financial_year }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted">No appraisals found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">{{ $appraisals->links() }}</div>
        </div>
    </div>
@endsection
