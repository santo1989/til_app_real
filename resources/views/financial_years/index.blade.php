@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Financial Years Management</h3>
            <a href="{{ route('financial-years.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Financial Year
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error') || $errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') ?? $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Revision Cutoff</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($financialYears as $fy)
                            <tr class="{{ $fy->is_active ? 'table-success' : '' }}">
                                <td>
                                    <strong>{{ $fy->label }}</strong>
                                    @if ($fy->is_active)
                                        <span class="badge bg-success ms-2">ACTIVE</span>
                                    @endif
                                </td>
                                <td>{{ optional($fy->start_date)->format('M d, Y') ?? '—' }}</td>
                                <td>{{ optional($fy->end_date)->format('M d, Y') ?? '—' }}</td>
                                <td>
                                    {{ optional($fy->revision_cutoff)->format('M d, Y') ?? '—' }}
                                    @if ($fy->revision_cutoff && $fy->isRevisionAllowed())
                                        <span class="badge bg-info">Open</span>
                                    @else
                                        <span class="badge bg-secondary">Locked</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($fy->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif ($fy->status === 'upcoming')
                                        <span class="badge bg-primary">Upcoming</span>
                                    @else
                                        <span class="badge bg-secondary">Closed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('financial-years.show', $fy) }}" class="btn btn-sm btn-info"
                                            title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('financial-years.edit', $fy) }}" class="btn btn-sm btn-warning"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if (!$fy->is_active)
                                            <form action="{{ route('financial-years.activate', $fy) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-success" title="Activate"
                                                    onclick="return confirm('Activate this financial year? This will deactivate all others.')">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if ($fy->is_active && $fy->status !== 'closed')
                                            <form action="{{ route('financial-years.close', $fy) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-secondary" title="Close"
                                                    onclick="return confirm('Close this financial year?')">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if (!$fy->is_active)
                                            <form action="{{ route('financial-years.destroy', $fy) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                                    onclick="return confirm('Delete this financial year? This cannot be undone.')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No financial years found. Create one to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <div class="alert alert-info">
                <strong><i class="fas fa-info-circle"></i> Note:</strong>
                <ul class="mb-0 mt-2">
                    <li>Only one financial year can be active at a time.</li>
                    <li>Revision cutoff is automatically set to 9 months from the start date.</li>
                    <li>Active financial years cannot be deleted.</li>
                    <li>All objectives and appraisals are linked to financial years.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
