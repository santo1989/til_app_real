@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4>Line Manager Dashboard</h4>
                    <p class="text-muted mb-0">Welcome, {{ auth()->user()->name }} — manage your team efficiently.</p>
                </div>
                <div>
                    <x-ui.button variant="primary" href="{{ route('objectives.team') }}">Team Objectives</x-ui.button>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Team Members</h6>
                            <h3>{{ $stats['team_size'] ?? '—' }}</h3>
                            <small class="text-muted">Active reports</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Team IDPs</h6>
                            <h3>{{ $stats['team_idps'] ?? '—' }}</h3>
                            <p class="text-muted">Development plans for your reports</p>
                            <a href="{{ route('idps.index', ['manager_id' => auth()->id()]) }}"
                                class="btn btn-sm btn-outline-info mt-2">View Team IDPs</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Pending Midterms</h6>
                            <h3>{{ $stats['pending_midterms'] ?? '—' }}</h3>
                            <a href="{{ route('appraisals.midterm') }}" class="btn btn-sm btn-outline-primary mt-2">Open
                                Midterms</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Pending Year-End</h6>
                            <h3>{{ $stats['pending_yearend'] ?? '—' }}</h3>
                            <a href="{{ route('appraisals.yearend') }}" class="btn btn-sm btn-outline-success mt-2">Open
                                Year-End</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
