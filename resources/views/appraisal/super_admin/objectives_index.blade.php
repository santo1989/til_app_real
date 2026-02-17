@extends('layouts.app')

@section('content')
    <div class="card card-responsive">
        <div class="card-header bg-warning text-white d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-bullseye"></i> All Objectives</h5>
            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                <span class="auto-refresh-badge">Auto-refresh: 30s</span>
                <button class="btn btn-sm btn-light" data-manual-refresh="objectives-table-container" data-refresh-url="{{ route('objectives.index') }}">
                    <i class="fas fa-sync-alt"></i> Refresh Now
                </button>
            </div>
        </div>
        <div class="card-body" id="objectives-table-container" data-auto-refresh="true" data-refresh-url="{{ route('objectives.index') }}" data-refresh-target="objectives-table-container">
            <div class="table-responsive-custom">
                <table class="table table-striped table-hover datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="hide-mobile">Weight</th>
                            <th>Status</th>
                            <th class="hide-mobile">FY</th>
                            <th class="hide-mobile">Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($objectives as $o)
                            <tr>
                                <td>{{ $o->id }}</td>
                                <td class="text-truncate-mobile">{{ $o->user->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-secondary badge-responsive">{{ ucfirst($o->type) }}</span></td>
                                <td>{{ Str::limit($o->description, 60) }}</td>
                                <td class="hide-mobile">{{ $o->weightage }}%</td>
                                <td>
                                    <span
                                        class="badge badge-responsive bg-{{ $o->status == 'set' ? 'success' : ($o->status == 'draft' ? 'warning' : 'info') }}">
                                        {{ ucfirst($o->status) }}
                                    </span>
                                </td>
                                <td class="hide-mobile">{{ $o->financial_year }}</td>
                                <td class="hide-mobile">{{ $o->creator->name ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
