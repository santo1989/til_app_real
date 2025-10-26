@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Departments</h5>
            <a class="btn btn-primary mb-2" href="{{ route('departments.create') }}">Create Department</a>
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Head</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($departments as $i => $d)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $d->name }}</td>
                            <td>{{ $d->head ? $d->head->name : '-' }}</td>
                            <td><a class="btn btn-sm btn-secondary" href="{{ route('departments.edit', $d) }}">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
