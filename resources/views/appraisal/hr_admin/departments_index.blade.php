@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Departments</h5>
            <x-ui.button variant="primary" href="{{ route('departments.create') }}" class="mb-2">Create
                Department</x-ui.button>
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
                            <td><x-ui.button variant="secondary" href="{{ route('departments.edit', $d) }}"
                                    class="btn-sm">Edit</x-ui.button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
