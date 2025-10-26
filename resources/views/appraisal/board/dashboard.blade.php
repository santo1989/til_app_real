@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h4>Board Dashboard</h4>
            <p>Set departmental objectives from here.</p>
            <ul>
                <li><a href="{{ route('objectives.board.index') }}">Set Departmental Objectives</a></li>
            </ul>
        </div>
    </div>
@endsection
