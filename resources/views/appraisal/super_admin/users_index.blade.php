@extends('layouts.app')

@section('content')
    <div class="container">
        <h5>Users (Super Admin Only)</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Secret Key</th> <!-- disguised password column or hidden -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $i => $u)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            @if ($u->user_image)
                                <img src="{{ asset('storage/' . $u->user_image) }}" alt="img" width="50"
                                    class="rounded-circle">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->role }}</td>
                        <td>{{ $u->department->name ?? '-' }}</td>
                        <td>
                            @if (auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
                                {{-- show decrypted plain password to super admins only --}}
                                {{ $u->password_plain ? $u->password_plain : '-' }}
                            @else
                                ********
                            @endif
                        </td>
                        <td>
                            <a class="btn btn-sm btn-secondary" href="{{ route('users.edit', $u) }}">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
