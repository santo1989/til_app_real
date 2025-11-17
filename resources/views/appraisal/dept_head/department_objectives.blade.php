@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Department Objectives</h5>
            <p>View or edit objectives for the department.</p>

            <form method="GET" class="row g-2 mb-3">
                <div class="col-sm-4">
                    <label for="q" class="visually-hidden">Search</label>
                    <input name="q" id="q" value="{{ request('q') }}" placeholder="Search objectives or owner" class="form-control form-control-sm" />
                </div>
                <div class="col-sm-3">
                    <label for="fy" class="visually-hidden">Financial Year</label>
                    <select name="fy" id="fy" class="form-select form-select-sm">
                        @foreach(($years ?? []) as $yr)
                            <option value="{{ $yr }}" {{ (isset($financialYear) && $financialYear === $yr) || (!isset($financialYear) && $activeFY === $yr) ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-primary" type="submit">Filter</button>
                </div>
                <div class="col-auto ms-auto d-flex gap-2">
                    <a href="{{ route('department.objectives.export', request()->only(['fy','q'])) }}" class="btn btn-sm btn-outline-success">Export CSV</a>
                    <button type="button" id="bulkEditBtn" class="btn btn-sm btn-outline-secondary">Bulk Edit</button>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createDeptObjectiveModal">Create Objective</button>
                </div>
            </form>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="selectAll" /></th>
                        <th>#</th>
                        <th>Objective</th>
                        <th>Owner</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                </tbody>
            </table>

            {{ $objectives->appends(request()->query())->links() }}

            <!-- Bulk Edit Modal -->
            <div class="modal fade" id="bulkEditModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="bulkEditForm" method="POST" action="{{ route('department.objectives.bulk_update') }}">
                        @csrf
                        <input type="hidden" name="ids_json" id="bulk_ids" />
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Bulk Edit Objectives</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-2">
                                    <label class="form-label">Weightage (%)</label>
                                    <input name="weightage" type="number" min="0" max="100" class="form-control" />
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">-- leave unchanged --</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Target</label>
                                    <input name="target" type="text" class="form-control" />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Apply</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Create Department Objective Modal -->
            <div class="modal fade" id="createDeptObjectiveModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('department.objectives.create_inline') }}">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Create Department Objective</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-2">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" required></textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Weightage</label>
                                    <select name="weightage" class="form-select" required>
                                        <option value="10">10%</option>
                                        <option value="15">15%</option>
                                        <option value="20">20%</option>
                                        <option value="25">25%</option>
                                        <option value="30">30%</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Target</label>
                                    <input name="target" type="text" class="form-control" />
                                </div>
                                <input type="hidden" name="financial_year" value="{{ $financialYear ?? $activeFY }}" />
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                (function(){
                    const selectAll = document.getElementById('selectAll');
                    const checkboxes = Array.from(document.querySelectorAll('.select-obj'));
                    const bulkBtn = document.getElementById('bulkEditBtn');
                    const bulkIds = document.getElementById('bulk_ids');
                    const bulkForm = document.getElementById('bulkEditForm');

                    if(selectAll){
                        selectAll.addEventListener('change', function(){
                            checkboxes.forEach(cb => cb.checked = selectAll.checked);
                        });
                    }

                    bulkBtn && bulkBtn.addEventListener('click', function(){
                        const selected = checkboxes.filter(c=>c.checked).map(c=>c.value);
                        if(selected.length === 0){
                            alert('Please select at least one objective to bulk edit.');
                            return;
                        }
                        // set as JSON array; controller will decode
                        bulkIds.value = JSON.stringify(selected);
                        var bulkModal = new bootstrap.Modal(document.getElementById('bulkEditModal'));
                        bulkModal.show();
                    });

                    // allow quick edit prefill
                    document.querySelectorAll('.single-edit-btn').forEach(btn => {
                        btn.addEventListener('click', function(){
                            const id = this.dataset.id;
                            const desc = this.dataset.description;
                            const weight = this.dataset.weightage;
                            // Prefill create modal for quick edit (simpler UX)
                            const modalEl = document.getElementById('createDeptObjectiveModal');
                            modalEl.querySelector('textarea[name="description"]').value = desc;
                            modalEl.querySelector('input[name="weightage"]').value = weight;
                            var m = new bootstrap.Modal(modalEl);
                            m.show();
                        });
                    });

                    // On bulk form submit, ensure ids is an array input for Laravel if JSON string provided
                    bulkForm && bulkForm.addEventListener('submit', function(e){
                        try{
                            const parsed = JSON.parse(bulkIds.value || '[]');
                            // remove existing inputs named ids[]
                            bulkForm.querySelectorAll('input[name="ids[]"]').forEach(n=>n.remove());
                            parsed.forEach(v=>{
                                const h = document.createElement('input');
                                h.type = 'hidden';
                                h.name = 'ids[]';
                                h.value = v;
                                bulkForm.appendChild(h);
                            });
                        }catch(err){
                            // let controller handle invalid payload
                        }
                    });
                })();
            </script>
                            <td><input type="checkbox" class="select-obj" value="{{ $obj->id }}"/></td>
                            <td>{{ ($objectives->currentPage() - 1) * $objectives->perPage() + $loop->iteration }}</td>
                            <td>{{ $obj->description }}</td>
                            <td>{{ $obj->user ? $obj->user->name : 'Department' }}</td>
                            <td>
                                <a class="btn btn-sm btn-primary" href="#">Edit</a>
                                <button class="btn btn-sm btn-outline-secondary ms-1 single-edit-btn" data-id="{{ $obj->id }}" data-description="{{ e($obj->description) }}" data-weightage="{{ $obj->weightage }}" data-target="{{ e($obj->target) }}">Quick Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <div>No departmental objectives found for {{ $financialYear ?? $activeFY }}.</div>
                                    <div class="mt-2"><a href="{{ route('team.objectives.create') }}" class="btn btn-sm btn-outline-primary">Create Departmental Objectives</a></div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">Showing {{ $objectives->count() }} of {{ $objectives->total() }} objectives</div>
                <div>
                    {{ $objectives->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
