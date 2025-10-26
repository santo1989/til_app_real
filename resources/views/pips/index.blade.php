@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h4>PIP Management</h4>
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
                <button class="btn btn-sm btn-primary mr-2">Filter</button>
                <a href="{{ route('pips.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                <a href="{{ route('pips.export') }}?{{ http_build_query(request()->query()) }}"
                    class="btn btn-sm btn-outline-success ml-2">Export CSV</a>
            </form>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Manager</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pips as $pip)
                        <tr>
                            <td>{{ $pip->id }}</td>
                            <td>
                                @if ($pip->user)
                                    <a href="{{ route('users.show', $pip->user->id) }}">{{ $pip->user->name }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($pip->user && $pip->user->lineManager)
                                    <a
                                        href="{{ route('users.show', $pip->user->lineManager->id) }}">{{ $pip->user->lineManager->name }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $pip->status }}</td>
                            <td>{{ $pip->reason }}</td>
                            <td>{{ optional($pip->created_at)->format('d-M-Y') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('pips.show', $pip->id) }}" class="btn btn-sm btn-primary">View</a>
                                @if ($pip->status !== 'closed')
                                    <form action="{{ route('pips.close', $pip->id) }}" method="POST"
                                        style="display:inline">@csrf
                                        <button class="btn btn-sm btn-danger">Close</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
