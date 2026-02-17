@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card card-responsive">
            <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users"></i> Users (Super Admin Only)</h5>
                <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                    <span class="auto-refresh-badge">Auto-refresh: 30s</span>
                    <button class="btn btn-sm btn-light" data-manual-refresh="users-table-container" data-refresh-url="{{ route('superadmin.users.index') }}">
                        <i class="fas fa-sync-alt"></i> Refresh Now
                    </button>
                </div>
            </div>
            <div class="card-body" id="users-table-container" data-auto-refresh="true" data-refresh-url="{{ route('superadmin.users.index') }}" data-refresh-target="users-table-container">
                <div class="table-responsive-custom">
                    <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th class="hide-mobile">Image</th>
                        <th>Name</th>
                        <th class="hide-mobile">Email</th>
                        <th>Role</th>
                        <th class="hide-mobile">Department</th>
                        <th class="hide-mobile">Secret Key</th>
                        <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $i => $u)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="hide-mobile">
                            @if ($u->user_image)
                                <img src="{{ asset('storage/' . $u->user_image) }}" alt="img" width="50"
                                    class="rounded-circle">
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-truncate-mobile">{{ $u->name }}</td>
                        <td class="hide-mobile">{{ $u->email }}</td>
                        <td><span class="badge bg-primary badge-responsive">{{ $u->role }}</span></td>
                        <td class="hide-mobile">{{ $u->department->name ?? '-' }}</td>
                        <td class="hide-mobile">
                            @if (auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
                                {{-- show decrypted plain password to super admins only --}}
                                {{ $u->password_plain ? $u->password_plain : '-' }}
                            @else
                                ********
                            @endif
                        </td>
                        <td>
                            <x-ui.button variant="secondary" href="{{ route('users.edit', $u) }}"
                                class="btn-sm">Edit</x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
