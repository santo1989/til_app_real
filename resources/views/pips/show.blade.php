@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h4>PIP #{{ $pip->id }} for {{ $pip->user->name }}</h4>
            <p><strong>Status:</strong> {{ $pip->status }}</p>
            <p><strong>Reason:</strong> {{ $pip->reason }}</p>
            <p><strong>Notes:</strong> {{ $pip->notes }}</p>
            <p><strong>Start:</strong> {{ $pip->start_date }} | <strong>End:</strong> {{ $pip->end_date }}</p>
            <p><strong>Appraisal:</strong> <a href="{{ route('appraisals.show', $pip->appraisal_id) }}">View Appraisal</a>
            </p>
            <a href="{{ route('pips.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
@endsection
