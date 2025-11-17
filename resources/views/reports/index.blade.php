@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h3>HR Reports</h3>
            <p class="text-muted">A lightweight reports landing page. Implement detailed reports as needed.</p>
            <div class="card">
                <div class="card-body">
                    <p><strong>Total appraisals:</strong> {{ $totalAppraisals ?? 0 }}</p>
                    <p><strong>Average total score:</strong> {{ is_null($avgScore) ? 'N/A' : number_format($avgScore, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
