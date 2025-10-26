@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-chart-line"></i> All Appraisals</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Rating</th>
                            <th>FY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appraisals as $a)
                            <tr>
                                <td>{{ $a->id }}</td>
                                <td>{{ $a->user->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-secondary">{{ str_replace('_', ' ', ucfirst($a->type)) }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $a->status === 'completed' ? 'success' : ($a->status === 'pending' ? 'warning' : 'info') }}">{{ ucfirst($a->status ?? 'n/a') }}</span>
                                </td>
                                <td>{{ $a->total_score ?? ($a->achievement_score ?? '-') }}</td>
                                <td>{{ ucfirst($a->rating ?? '-') }}</td>
                                <td>{{ $a->financial_year }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
