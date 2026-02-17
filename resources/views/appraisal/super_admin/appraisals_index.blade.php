@extends('layouts.app')

@section('content')
    <div class="card card-responsive">
        <div class="card-header bg-success text-white d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-chart-line"></i> All Appraisals</h5>
            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                <span class="auto-refresh-badge">Auto-refresh: 30s</span>
                <button class="btn btn-sm btn-light" data-manual-refresh="appraisals-table-container" data-refresh-url="{{ route('appraisals.index') }}">
                    <i class="fas fa-sync-alt"></i> Refresh Now
                </button>
            </div>
        </div>
        <div class="card-body" id="appraisals-table-container" data-auto-refresh="true" data-refresh-url="{{ route('appraisals.index') }}" data-refresh-target="appraisals-table-container">
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
                        @foreach ($appraisals as $a)
                            <tr>
                                <td>{{ $a->id }}</td>
                                <td class="text-truncate-mobile">{{ $a->user->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-secondary badge-responsive">{{ str_replace('_', ' ', ucfirst($a->type)) }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge badge-responsive bg-{{ $a->status === 'completed' ? 'success' : ($a->status === 'pending' ? 'warning' : 'info') }}">{{ ucfirst($a->status ?? 'n/a') }}</span>
                                </td>
                                <td class="hide-mobile">{{ $a->total_score ?? ($a->achievement_score ?? '-') }}</td>
                                <td class="hide-mobile">{{ ucfirst($a->rating ?? '-') }}</td>
                                <td class="hide-mobile">{{ $a->financial_year }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
