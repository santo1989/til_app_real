@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="mb-4">
            <h3>Financial Year: {{ $financialYear->label }}</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('financial-years.index') }}">Financial Years</a></li>
                    <li class="breadcrumb-item active">{{ $financialYear->label }}</li>
                </ol>
            </nav>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Financial Year Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Label:</th>
                                <td>
                                    <strong>{{ $financialYear->label }}</strong>
                                    @if ($financialYear->is_active)
                                        <span class="badge bg-success">ACTIVE</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if ($financialYear->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif ($financialYear->status === 'upcoming')
                                        <span class="badge bg-primary">Upcoming</span>
                                    @else
                                        <span class="badge bg-secondary">Closed</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Start Date:</th>
                                <td>{{ optional($financialYear->start_date)->format('F d, Y') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>End Date:</th>
                                <td>{{ optional($financialYear->end_date)->format('F d, Y') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Revision Cutoff:</th>
                                <td>
                                    {{ optional($financialYear->revision_cutoff)->format('F d, Y') ?? '—' }}
                                    @if ($financialYear->revision_cutoff && $financialYear->isRevisionAllowed())
                                        <span class="badge bg-info">Open for Revisions</span>
                                    @else
                                        <span class="badge bg-secondary">Locked</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Duration:</th>
                                <td>
                                    @if ($financialYear->start_date && $financialYear->end_date)
                                        {{ $financialYear->start_date->diffInDays($financialYear->end_date) }} days
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <div class="text-end mt-3">
                            <x-ui.button variant="warning" href="{{ route('financial-years.edit', $financialYear) }}">
                                <i class="fas fa-edit"></i> Edit
                            </x-ui.button>
                            @if (!$financialYear->is_active)
                                <form action="{{ route('financial-years.activate', $financialYear) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <x-ui.button variant="success" type="submit"
                                        onclick="return confirm('Activate this financial year? This will deactivate all others.')">
                                        <i class="fas fa-check-circle"></i> Activate
                                    </x-ui.button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <h3 class="text-primary mb-0">
                                        {{ $financialYear->objectives ? $financialYear->objectives->count() : 0 }}</h3>
                                    <small class="text-muted">Objectives</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <h3 class="text-success mb-0">
                                        {{ $financialYear->appraisals ? $financialYear->appraisals->count() : 0 }}</h3>
                                    <small class="text-muted">Appraisals</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h3 class="text-warning mb-0">
                                        {{ $financialYear->objectives ? $financialYear->objectives->where('type', 'departmental')->count() : 0 }}
                                    </h3>
                                    <small class="text-muted">Departmental</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h3 class="text-info mb-0">
                                        {{ $financialYear->objectives ? $financialYear->objectives->where('type', 'individual')->count() : 0 }}
                                    </h3>
                                    <small class="text-muted">Individual</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <i class="fas fa-calendar-alt text-primary"></i>
                        <strong>Start Date:</strong>
                        {{ optional($financialYear->start_date)->format('F d, Y') ?? '—' }}
                    </div>
                    <div class="timeline-item">
                        <i class="fas fa-clock text-warning"></i>
                        <strong>6-Month Review:</strong>
                        {{ $financialYear->start_date ? $financialYear->start_date->copy()->addMonths(6)->format('F d, Y') : '—' }}
                    </div>
                    <div class="timeline-item">
                        <i class="fas fa-lock text-danger"></i>
                        <strong>Revision Cutoff (9 months):</strong>
                        {{ optional($financialYear->revision_cutoff)->format('F d, Y') ?? '—' }}
                    </div>
                    <div class="timeline-item">
                        <i class="fas fa-flag-checkered text-success"></i>
                        <strong>End Date:</strong>
                        {{ optional($financialYear->end_date)->format('F d, Y') ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline-item {
            padding: 15px 0 15px 40px;
            position: relative;
            border-left: 2px solid #dee2e6;
        }

        .timeline-item:last-child {
            border-left: 2px solid transparent;
        }

        .timeline-item i {
            position: absolute;
            left: -12px;
            top: 15px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: white;
            padding: 4px;
        }
    </style>
@endsection
