@extends('layouts.app')

@section('content')
    <div class="card card-responsive">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> All IDPs</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-sync-alt"></i> Auto-refresh: 30s
                </span>
                <button class="btn btn-sm btn-outline-light" onclick="AutoRefresh.manualRefresh('idps-table-container')">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body" id="idps-table-container" 
             data-auto-refresh="true" 
             data-refresh-url="{{ route('super_admin.idps_index') }}"
             data-refresh-target="#idps-table-container" id="idps-table-container" 
             data-auto-refresh="true" 
             data-refresh-url="{{ route('super_admin.idps_index') }}"
             data-refresh-target="#idps-table-container">
            <div class="table-responsive-custom">
                <table class="table table-striped datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th class="hide-mobile">Description</th>
                            <th>Status</th>
                            <th class="hide-mobile">Review Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($idps as $i)
                            <tr>
                                <td>{{ $i->id }}</td>
                                <td class="text-truncate-mobile">{{ $i->user->name ?? 'N/A' }}</td>
                                <td class="hide-mobile">{{ Str::limit($i->description, 60) }}</td>
                                <td>
                                    <span
                                        class="badge badge-responsive bg-{{ $i->status === 'completed' ? 'success' : ($i->status === 'pending' ? 'warning' : 'info') }}">{{ ucfirst($i->status ?? 'n/a') }}</span>
                                </td>
                                <td class="hide-mobile">{{ optional($i->review_date)->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
