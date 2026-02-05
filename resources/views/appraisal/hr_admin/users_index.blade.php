@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Users</h5>
            <a class="btn btn-outline-primary mb-2" href="{{ route('users.create') }}">Create User</a>
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $i => $u)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->role }}</td>
                            <td>
                                @can('view', $u)
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('users.show', $u) }}">Show</a>
                                @endcan
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.edit', $u) }}">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
