@extends('layouts.app')

@section('content')
    <div class="card card-responsive">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0"><i class="fas fa-tasks"></i> Pending Approvals</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-sync-alt"></i> Auto-refresh: 30s
                </span>
                <button class="btn btn-sm btn-outline-light" onclick="AutoRefresh.manualRefresh('approvals-container')">
                    <i class="fas fa-sync"></i> Refresh
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-light">Back to Dashboard</a>
            </div>
        </div>
        <div class="card-body" id="approvals-container" 
             data-auto-refresh="true" 
             data-refresh-url="{{ route('line_manager.approvals') }}?{{ http_build_query(request()->query()) }}"
             data-refresh-target="#approvals-container">

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                placeholder="Search employee name, email or description">
        </div>
        <div class="col-md-2">
            <input type="text" name="fy" value="{{ request('fy') }}" class="form-control"
                placeholder="Financial Year (e.g. 2025-26)">
        </div>
        <div class="col-md-2">
            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary">Filter</button>
        </div>
    </form>

    @if ($pending->isEmpty())
        <div class="alert alert-info">No pending objectives for your direct reports.</div>
    @else
        <form id="bulk-actions-form" method="POST" action="{{ route('objectives.bulk_approve') }}">
            @csrf
            <input type="hidden" name="_action" value="approve">
            <div class="mb-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-success btn-sm" id="bulk-approve">Bulk Approve</button>
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                    data-bs-target="#bulkRejectModal">Bulk Reject</button>
                <div class="ms-auto"><small class="text-muted">Selected: <span id="selected-count">0</span></small></div>
            </div>

            <div class="table-responsive-custom">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>#</th>
                        <th>Employee</th>
                        <th class="hide-mobile">Counts (set / pending / rejected)</th>
                        <th class="hide-mobile">Midterm</th>
                        <th>Description</th>
                        <th class="hide-mobile">Weightage</th>
                        <th class="hide-mobile">Target</th>
                        <th class="hide-mobile">Submitted At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pending as $obj)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $obj->id }}" class="select-item"></td>
                            <td>{{ $obj->id }}</td>
                            <td class="text-truncate-mobile">
                                {{ $obj->user?->name ?? 'N/A' }}<br>
                                <small class="text-muted">{{ $obj->user?->email }}</small>
                            </td>
                            <td class="hide-mobile">
                                @php $c = $counts[$obj->user_id] ?? []; @endphp
                                <span class="badge badge-responsive bg-success">{{ $c['set'] ?? 0 }}</span>
                                <span class="badge badge-responsive bg-warning text-dark">{{ $c['pending'] ?? 0 }}</span>
                                <span class="badge badge-responsive bg-danger">{{ $c['rejected'] ?? 0 }}</span>
                            </td>
                            <td class="hide-mobile">
                                @if (isset($midterm[$obj->user_id]))
                                    <strong>{{ $midterm[$obj->user_id] }}%</strong>
                                @else
                                    <small class="text-muted">—</small>
                                @endif
                                <div class="mt-1">
                                    <button class="btn btn-sm btn-outline-primary record-midterm-btn"
                                        data-user-id="{{ $obj->user_id }}" data-objective-id="{{ $obj->id }}"
                                        data-fy="{{ $obj->financial_year }}">
                                        Record
                                    </button>
                                </div>
                            </td>
                            <td>{{ $obj->description }}
                                @if ($obj->rejection_reason)
                                    <div><small class="text-danger">Rejected: {{ $obj->rejection_reason }}</small></div>
                                @endif
                            </td>
                            <td class="hide-mobile">{{ $obj->weightage }}%</td>
                            <td class="hide-mobile">{{ $obj->target }}</td>
                            <td class="hide-mobile">{{ optional($obj->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <form action="{{ route('objectives.approve', $obj) }}" method="POST"
                                    style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                                </form>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse"
                                    data-bs-target="#reject-form-{{ $obj->id }}">Reject</button>
                                <div class="collapse mt-2" id="reject-form-{{ $obj->id }}">
                                    <form action="{{ route('objectives.reject', $obj) }}" method="POST" class="d-flex">
                                        @csrf
                                        <input type="text" name="reason" class="form-control form-control-sm me-2"
                                            placeholder="Reason (optional)">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Confirm Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </form>

        <div class="d-flex justify-content-center">{{ $pending->links() }}</div>

        <!-- Bulk reject modal -->
        <div class="modal fade" id="bulkRejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bulk Reject</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="bulk-reject-form" method="POST" action="{{ route('objectives.bulk_reject') }}">
                            @csrf
                            <input type="hidden" name="ids" id="bulk-reject-ids">
                            <div class="mb-3">
                                <label>Reason (optional)</label>
                                <input type="text" name="reason" class="form-control">
                            </div>
                        </form>
                        <p class="text-muted">This will mark selected objectives as rejected and record the reason.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-outline-danger" id="confirm-bulk-reject">Reject
                            Selected</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Select all / count
            document.addEventListener('DOMContentLoaded', function() {
                const selectAll = document.getElementById('select-all');
                const items = document.querySelectorAll('.select-item');
                const selectedCount = document.getElementById('selected-count');

                function updateCount() {
                    const n = document.querySelectorAll('.select-item:checked').length;
                    selectedCount.textContent = n;
                }
                if (selectAll) selectAll.addEventListener('change', function() {
                    items.forEach(i => i.checked = this.checked);
                    updateCount();
                });
                items.forEach(i => i.addEventListener('change', updateCount));

                // Prepare bulk reject form
                const bulkRejectModal = document.getElementById('bulkRejectModal');
                document.getElementById('confirm-bulk-reject').addEventListener('click', function() {
                    const checked = Array.from(document.querySelectorAll('.select-item:checked')).map(i => i
                        .value);
                    if (checked.length === 0) {
                        alert('Select at least one objective to reject');
                        return;
                    }
                    document.getElementById('bulk-reject-ids').value = JSON.stringify(checked);
                    document.getElementById('bulk-reject-form').submit();
                });

                // Record midterm modal
                const recordBtns = document.querySelectorAll('.record-midterm-btn');
                const midtermModalHtml = `
                                <div class="modal fade" id="midtermModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Record Midterm Progress</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form id="midterm-form" method="POST" action="{{ route('objectives.midterm.store') }}">
                                                        @csrf
                                                        <input type="hidden" name="user_id" id="midterm-user-id">
                                                        <input type="hidden" name="objective_id" id="midterm-objective-id">
                                                        <input type="hidden" name="financial_year" id="midterm-fy">
                                                        <div class="mb-3">
                                                                <label>Progress (%)</label>
                                                                <input type="number" name="progress_percent" id="midterm-progress" class="form-control" min="0" max="100" required>
                                                        </div>
                                                        <div class="mb-3">
                                                                <label>Notes</label>
                                                                <textarea name="notes" class="form-control" rows="3" id="midterm-notes"></textarea>
                                                        </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="button" class="btn btn-outline-primary" id="midterm-save">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;

                // Append modal to body when needed
                document.body.insertAdjacentHTML('beforeend', midtermModalHtml);
                const midtermModalEl = document.getElementById('midtermModal');
                const midtermModal = new bootstrap.Modal(midtermModalEl);
                recordBtns.forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const userId = this.dataset.userId;
                        const objectiveId = this.dataset.objectiveId;
                        const fy = this.dataset.fy;
                        document.getElementById('midterm-user-id').value = userId;
                        document.getElementById('midterm-objective-id').value = objectiveId;
                        document.getElementById('midterm-fy').value = fy;
                        // Clear while loading
                        document.getElementById('midterm-progress').value = '';
                        document.getElementById('midterm-notes').value = '';
                        // Fetch latest midterm entry via AJAX and pre-fill if available
                        try {
                            const url = `{{ url('/appraisal/approvals/midterm') }}/${userId}` + (
                                fy ? `?fy=${encodeURIComponent(fy)}` : '');
                            const res = await fetch(url, {
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            if (res.ok) {
                                const body = await res.json();
                                if (body.data && body.data.length > 0) {
                                    const latest = body.data[0];
                                    if (latest.progress_percent !== undefined && latest
                                        .progress_percent !== null) {
                                        document.getElementById('midterm-progress').value = latest
                                            .progress_percent;
                                    }
                                    if (latest.notes) {
                                        document.getElementById('midterm-notes').value = latest
                                            .notes;
                                    }
                                }
                            }
                        } catch (err) {
                            // ignore - leave fields blank
                            console.error('Failed to fetch midterm:', err);
                        }
                        midtermModal.show();
                    });
                });
                document.getElementById('midterm-save').addEventListener('click', function() {
                    document.getElementById('midterm-form').submit();
                });
            });
        </script>
        </div>
    </div>
    @endif
@endsection
