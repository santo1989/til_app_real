@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Department Objectives</h5>
            <p>View or edit objectives for the department.</p>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Objective</th>
                        <th>Owner</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($objectives as $i => $obj)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $obj->description }}</td>
                            <td>{{ $obj->user ? $obj->user->name : 'Department' }}</td>
                            <td><a class="btn btn-sm btn-primary" href="#">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
