@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> All IDPs</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Review Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($idps as $i)
                            <tr>
                                <td>{{ $i->id }}</td>
                                <td>{{ $i->user->name ?? 'N/A' }}</td>
                                <td>{{ Str::limit($i->description, 60) }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $i->status === 'completed' ? 'success' : ($i->status === 'pending' ? 'warning' : 'info') }}">{{ ucfirst($i->status ?? 'n/a') }}</span>
                                </td>
                                <td>{{ optional($i->review_date)->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
