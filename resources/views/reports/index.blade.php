@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card card-responsive">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> HR Reports</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-sync-alt"></i> Auto-refresh: 30s
                            </span>
                            <button class="btn btn-sm btn-outline-light" onclick="AutoRefresh.manualRefresh('reports-container')">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body" id="reports-container" 
                         data-auto-refresh="true" 
                         data-refresh-url="{{ route('reports.index') }}"
                         data-refresh-target="#reports-container">
                        <p class="text-muted">A lightweight reports landing page. Implement detailed reports as needed.</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="text-muted">Total Appraisals</h6>
                                        <h3 class="mb-0">{{ $totalAppraisals ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h6 class="text-muted">Average Total Score</h6>
                                        <h3 class="mb-0">{{ is_null($avgScore) ? 'N/A' : number_format($avgScore, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
