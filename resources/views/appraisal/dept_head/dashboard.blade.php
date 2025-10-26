@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h4>Department Head Dashboard</h4>
            <p>Approve appraisals and view department objectives.</p>
            <ul>
                <li><a href="{{ route('objectives.department') }}">Department Objectives</a></li>
                <li><a href="#">Approve Appraisals</a></li>
            </ul>
        </div>
    </div>
@endsection
