@extends('layouts.app')
@section('content')
    <div class="card card-responsive">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0"><i class="fas fa-users"></i> Users</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-sync-alt"></i> Auto-refresh: 30s
                </span>
                <button class="btn btn-sm btn-outline-light" onclick="AutoRefresh.manualRefresh('users-container')">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body" id="users-container" 
             data-auto-refresh="true" 
             data-refresh-url="{{ route('hr_admin.users_index') }}"
             data-refresh-target="#users-container">
            <a class="btn btn-outline-primary mb-2" href="{{ route('users.create') }}">Create User</a>
            <div class="table-responsive-custom">
                <table class="table datatable">
                    <thead class="table-light">
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
                            <td class="text-truncate-mobile">{{ $u->name }}</td>
                            <td class="hide-mobile">{{ $u->email }}</td>
                            <td><span class="badge badge-responsive bg-primary">{{ $u->role }}</span></td>
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
    </div>
@endsection
