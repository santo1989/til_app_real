@extends('layouts.app')
@section('content')
    <div class="card card-responsive">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Reports</h5>
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
             data-refresh-url="{{ route('hr_admin.reports_index') }}"
             data-refresh-target="#reports-container">
            <p>Generate exports for appraisals, objectives and IDPs.</p>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-primary" href="#">Export Appraisals (PDF)</a>
                <a class="btn btn-outline-secondary" href="#">Export Excel</a>
            </div>
        </div>
    </div>
@endsection
