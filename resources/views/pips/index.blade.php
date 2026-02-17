@extends('layouts.app')
@section('content')
    <div class="card card-responsive">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> PIP Management</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-sync-alt"></i> Auto-refresh: 30s
                </span>
                <button class="btn btn-sm btn-outline-light" onclick="AutoRefresh.manualRefresh('pips-table-container')">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body" id="pips-table-container" 
             data-auto-refresh="true" 
             data-refresh-url="{{ route('pips.index') }}?{{ http_build_query(request()->query()) }}"
             data-refresh-target="#pips-table-container">
            <form method="GET" class="form-inline mb-3">
                <label class="mr-2">Status</label>
                <select name="status" class="form-control form-control-sm mr-2">
                    <option value="">Any</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
                <label class="mr-2">Start from</label>
                <input type="date" name="start_date_from" value="{{ request('start_date_from') }}"
                    class="form-control form-control-sm mr-2">
                <label class="mr-2">to</label>
                <input type="date" name="start_date_to" value="{{ request('start_date_to') }}"
                    class="form-control form-control-sm mr-2">
                <label class="mr-2">Dept</label>
                <select name="department_id" class="form-control form-control-sm mr-2">
                    <option value="">Any</option>
                    @if (isset($departments))
                        @foreach ($departments as $d)
                            <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}</option>
                        @endforeach
                    @endif
                </select>
                <label class="mr-2">Manager</label>
                <select name="manager_id" class="form-control form-control-sm mr-2">
                    <option value="">Any</option>
                    @if (isset($managers))
                        @foreach ($managers as $m)
                            <option value="{{ $m->id }}" {{ request('manager_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->name }}</option>
                        @endforeach
                    @endif
                </select>
                <button class="btn btn-sm btn-outline-primary mr-2">Filter</button>
                <a href="{{ route('pips.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                <a href="{{ route('pips.export') }}?{{ http_build_query(request()->query()) }}"
                    class="btn btn-sm btn-outline-success ml-2">Export CSV</a>
            </form>
            <div class="table-responsive-custom">
                <table class="table table-striped">
                    <thead class="table-light">
                        <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th class="hide-mobile">Manager</th>
                        <th>Status</th>
                        <th class="hide-mobile">Reason</th>
                        <th class="hide-mobile">Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pips as $pip)
                        <tr>
                            <td>{{ $pip->id }}</td>
                            <td class="text-truncate-mobile">
                                @if ($pip->user)
                                    <a href="{{ route('users.show', $pip->user->id) }}">{{ $pip->user->name }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="hide-mobile">
                                @if ($pip->user && $pip->user->lineManager)
                                    <a
                                        href="{{ route('users.show', $pip->user->lineManager->id) }}">{{ $pip->user->lineManager->name }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td><span class="badge badge-responsive bg-{{ $pip->status === 'closed' ? 'secondary' : 'warning' }}">{{ $pip->status }}</span></td>
                            <td class="hide-mobile">{{ $pip->reason }}</td>
                            <td class="hide-mobile">{{ optional($pip->created_at)->format('d-M-Y') ?? '—' }}</td>
                            <td>
                                <div class="btn-group-mobile">
                                    <a href="{{ route('pips.show', $pip->id) }}"
                                        class="btn btn-sm btn-outline-primary">View</a>
                                    @if ($pip->status !== 'closed')
                                        <form action="{{ route('pips.close', $pip->id) }}" method="POST"
                                            style="display:inline">@csrf
                                            <button class="btn btn-sm btn-outline-danger">Close</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $pips->firstItem() ?? 0 }} to {{ $pips->lastItem() ?? 0 }} of {{ $pips->total() }} records
                </div>
                <div>
                    {{ $pips->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
