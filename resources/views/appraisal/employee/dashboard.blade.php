@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h4>Employee Dashboard</h4>
            <p class="text-muted">Welcome, {{ auth()->user()->name }}. Quick access to your objectives and reviews.</p>

            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>My Objectives</h6>
                            <h3>{{ $stats['my_objectives'] ?? '—' }}</h3>
                            <a href="{{ route('objectives.my') }}" class="btn btn-sm btn-outline-primary mt-2">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Midterm</h6>
                            <h3>{{ $stats['midterm_due'] ?? '—' }}</h3>
                            <a href="{{ route('appraisals.midterm') }}"
                                class="btn btn-sm btn-outline-warning mt-2">Start</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>Year-End</h6>
                            <h3>{{ $stats['yearend_due'] ?? '—' }}</h3>
                            <a href="{{ route('appraisals.yearend') }}"
                                class="btn btn-sm btn-outline-success mt-2">Start</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h6>My IDPs</h6>
                            <h3>{{ $stats['my_idps'] ?? '—' }}</h3>
                            <a href="{{ route('idp.index') }}" class="btn btn-sm btn-outline-secondary mt-2">Open</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
