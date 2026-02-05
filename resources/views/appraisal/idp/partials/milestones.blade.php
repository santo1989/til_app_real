<div class="card mt-3">
    <div class="card-body">
        <h6>Milestones</h6>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @php
            // Use policy to decide if current user can mark attainment
            $globalCanAttain = auth()->user()->can('attain', $idp);
        @endphp
        <table class="table table-sm" id="milestones-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Attainment</th>
                    <th>Visible Demonstration</th>
                    <th>HR Input</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="milestones-body">
                @foreach ($idp->milestones as $milestone)
                    <tr data-id="{{ $milestone->id }}">
                        <td class="m-title">{{ $milestone->title }}</td>
                        <td class="m-start">{{ $milestone->start_date?->format('Y-m-d') ?? 'N/A' }}</td>
                        <td class="m-end">{{ $milestone->end_date?->format('Y-m-d') ?? 'N/A' }}</td>
                        <td class="m-progress">{{ $milestone->progress ?? 0 }}%</td>
                        <td class="m-status">{{ ucfirst(str_replace('_', ' ', $milestone->status)) }}</td>
                        <td class="m-attainment">{{ $milestone->attainment ? 'Yes' : 'No' }}</td>
                        <td class="m-visible">
                            {{ \Illuminate\Support\Str::limit($milestone->visible_demonstration, 80) }}</td>
                        <td class="m-hr">{{ \Illuminate\Support\Str::limit($milestone->hr_input, 80) }}</td>
                        <td class="m-actions">
                            @can('update', $idp)
                                <button class="btn btn-sm btn-outline-secondary edit-milestone">Edit</button>
                                <button class="btn btn-sm btn-outline-danger delete-milestone">Remove</button>
                            @endcan
                            @if (auth()->user()->can('attain', $idp))
                                <button class="btn btn-sm btn-outline-success ms-1 attain-milestone">Mark/Update
                                    Attainment</button>
                            @endif
                        </td>
                    </tr>
                    <tr class="edit-row d-none" data-id="edit-{{ $milestone->id }}">
                        <td colspan="9">
                            <form class="edit-milestone-form" data-id="{{ $milestone->id }}">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-3"><input name="title" class="form-control"
                                            value="{{ $milestone->title }}" required></div>
                                    <div class="col-md-2"><input type="date" name="start_date" class="form-control"
                                            value="{{ $milestone->start_date?->format('Y-m-d') }}"></div>
                                    <div class="col-md-2"><input type="date" name="end_date" class="form-control"
                                            value="{{ $milestone->end_date?->format('Y-m-d') }}"></div>
                                    <div class="col-md-2"><input type="number" name="progress" class="form-control"
                                            min="0" max="100" value="{{ $milestone->progress ?? 0 }}">
                                    </div>
                                    <div class="col-md-2">
                                        <select name="status" class="form-control">
                                            <option value="open"
                                                {{ $milestone->status == 'open' ? 'selected' : '' }}>Open</option>
                                            <option value="in_progress"
                                                {{ $milestone->status == 'in_progress' ? 'selected' : '' }}>In Progress
                                            </option>
                                            <option value="completed"
                                                {{ $milestone->status == 'completed' ? 'selected' : '' }}>Completed
                                            </option>
                                            <option value="blocked"
                                                {{ $milestone->status == 'blocked' ? 'selected' : '' }}>Blocked
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-1"><button class="btn btn-sm btn-outline-primary">Save</button>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @can('update', $idp)
            <hr>
            <h6>Add Milestone</h6>
            <form id="add-milestone-form">
                @csrf
                <div class="row">
                    <div class="col-md-3"><input name="title" class="form-control" placeholder="Title" required></div>
                    <div class="col-md-2"><input type="date" name="start_date" class="form-control"></div>
                    <div class="col-md-2"><input type="date" name="end_date" class="form-control"></div>
                    <div class="col-md-2"><input type="number" name="progress" class="form-control" min="0"
                            max="100" placeholder="0"></div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>
                    <div class="col-md-1"><button class="btn btn-outline-primary">Add</button></div>
                </div>
            </form>
        @endcan
    </div>
</div>

<!-- Attainment Modal -->
<div class="modal fade" id="attainModal" tabindex="-1" aria-labelledby="attainModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="attain-form">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" id="attain-milestone-id" name="milestone_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="attainModalLabel">Mark Milestone Attainment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Attained?</label>
                        <select name="attainment" id="attain-attained" class="form-control">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Visible Demonstration (describe evidence)</label>
                        <textarea id="attain-visible" name="visible_demonstration" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">HR Input / Comments</label>
                        <textarea id="attain-hr" name="hr_input" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addForm = document.getElementById('add-milestone-form');
            const milestonesBody = document.getElementById('milestones-body');
            const idpId = {{ $idp->id }};
            const CAN_ATTAIN = @json($globalCanAttain);

            function buildRows(milestones) {
                let html = '';
                milestones.forEach(m => {
                    const attainedText = m.attainment ? 'Yes' : 'No';
                    const visible = (m.visible_demonstration || '');
                    const hrInput = (m.hr_input || '');
                    html += `
            <tr data-id="${m.id}" data-attainment="${m.attainment?1:0}" data-visible="${escapeAttr(visible)}" data-hr="${escapeAttr(hrInput)}">
                <td class="m-title">${escapeHtml(m.title)}</td>
                <td class="m-start">${m.start_date ?? 'N/A'}</td>
                <td class="m-end">${m.end_date ?? 'N/A'}</td>
                <td class="m-progress">${m.progress ?? 0}%</td>
                <td class="m-status">${escapeHtml(capitalize(m.status))}</td>
                <td class="m-attainment">${escapeHtml(attainedText)}</td>
                <td class="m-visible">${escapeHtml(visible.substring(0, 80))}</td>
                <td class="m-hr">${escapeHtml(hrInput.substring(0, 80))}</td>
                <td class="m-actions">
                    <button class="btn btn-sm btn-outline-secondary edit-milestone">Edit</button>
                    <button class="btn btn-sm btn-outline-danger delete-milestone">Remove</button>
                    ${CAN_ATTAIN ? '<button class="btn btn-sm btn-outline-success ms-1 attain-milestone">Mark/Update Attainment</button>' : ''}
                </td>
            </tr>
            <tr class="edit-row d-none" data-id="edit-${m.id}">
                <td colspan="9">
                    <form class="edit-milestone-form" data-id="${m.id}">
                        <div class="row">
                            <div class="col-md-3"><input name="title" class="form-control" value="${escapeAttr(m.title)}" required></div>
                            <div class="col-md-2"><input type="date" name="start_date" class="form-control" value="${m.start_date ?? ''}"></div>
                            <div class="col-md-2"><input type="date" name="end_date" class="form-control" value="${m.end_date ?? ''}"></div>
                            <div class="col-md-2"><input type="number" name="progress" class="form-control" min="0" max="100" value="${m.progress ?? 0}"></div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="open" ${m.status=='open'?'selected':''}>Open</option>
                                    <option value="in_progress" ${m.status=='in_progress'?'selected':''}>In Progress</option>
                                    <option value="completed" ${m.status=='completed'?'selected':''}>Completed</option>
                                    <option value="blocked" ${m.status=='blocked'?'selected':''}>Blocked</option>
                                </select>
                            </div>
                            <div class="col-md-1"><button class="btn btn-sm btn-outline-primary">Save</button></div>
                        </div>
                    </form>
                </td>
            </tr>
            `;
                });
                milestonesBody.innerHTML = html;
            }

            function escapeHtml(s) {
                return (s || '').replace(/[&<>"']/g, function(c) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": "&#39;"
                    } [c];
                });
            }

            function escapeAttr(s) {
                return escapeHtml(s);
            }

            function capitalize(s) {
                if (!s) return '';
                return s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
            }

            async function postJson(url, data) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(data)
                });
                return resp.json();
            }

            async function putJson(url, data) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const resp = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(data)
                });
                return resp.json();
            }

            async function deleteJson(url) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const resp = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                });
                return resp.json();
            }

            // Add handler
            if (addForm) {
                addForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const fd = new FormData(addForm);
                    const data = Object.fromEntries(fd.entries());
                    const url = `{{ route('idps.milestones.store', ['idp' => $idp->id]) }}`;
                    try {
                        const json = await postJson(url, data);
                        if (json.html) {
                            // append rendered row HTML
                            milestonesBody.insertAdjacentHTML('beforeend', json.html);
                        }
                        addForm.reset();
                    } catch (err) {
                        console.error(err);
                        alert('Error adding milestone');
                    }
                });
            }

            // Delegate edit and delete
            document.addEventListener('click', function(e) {
                if (e.target.matches('.edit-milestone')) {
                    const tr = e.target.closest('tr');
                    const id = tr.getAttribute('data-id');
                    const editRow = document.querySelector(`tr.edit-row[data-id="edit-${id}"]`);
                    if (editRow) editRow.classList.toggle('d-none');
                }
                if (e.target.matches('.delete-milestone')) {
                    if (!confirm('Remove milestone?')) return;
                    const tr = e.target.closest('tr');
                    const id = tr.getAttribute('data-id');
                    const url = `/idps/${idpId}/milestones/${id}`;
                    deleteJson(url).then(json => {
                        if (json.deleted) {
                            // remove main row and edit-row if present
                            const main = document.querySelector(`tr[data-id="${id}"]`);
                            if (main) main.remove();
                            const edit = document.querySelector(
                                `tr.edit-row[data-id="edit-${id}"]`);
                            if (edit) edit.remove();
                        }
                    }).catch(() => alert('Delete failed'));
                }
                if (e.target.matches('.attain-milestone')) {
                    const tr = e.target.closest('tr');
                    const id = tr.getAttribute('data-id');
                    const currentAtt = tr.dataset.attainment || '0';
                    const currentVisible = tr.dataset.visible || '';
                    const currentHr = tr.dataset.hr || '';
                    document.getElementById('attain-milestone-id').value = id;
                    document.getElementById('attain-attained').value = currentAtt ? currentAtt : '0';
                    document.getElementById('attain-visible').value = currentVisible;
                    document.getElementById('attain-hr').value = currentHr;
                    // show modal
                    const modalEl = document.getElementById('attainModal');
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            });

            document.addEventListener('submit', function(e) {
                if (e.target.matches('.edit-milestone-form')) {
                    e.preventDefault();
                    const id = e.target.getAttribute('data-id');
                    const fd = new FormData(e.target);
                    const data = Object.fromEntries(fd.entries());
                    const url = `/idps/${idpId}/milestones/${id}`;
                    putJson(url, data).then(json => {
                        if (json.html) {
                            // replace existing row + edit row with returned HTML
                            const main = document.querySelector(`tr[data-id="${id}"]`);
                            const edit = document.querySelector(
                                `tr.edit-row[data-id="edit-${id}"]`);
                            if (main) main.remove();
                            if (edit) edit.remove();
                            // insert new html at end of tbody (or you could insert at previous position)
                            milestonesBody.insertAdjacentHTML('beforeend', json.html);
                        }
                    }).catch(() => alert('Update failed'));
                }
                if (e.target && e.target.id === 'attain-form') {
                    e.preventDefault();
                    const id = document.getElementById('attain-milestone-id').value;
                    const fd = new FormData(e.target);
                    const data = Object.fromEntries(fd.entries());
                    const url = `/idps/${idpId}/milestones/${id}/attain`;
                    postJson(url, data).then(json => {
                        if (json.html) {
                            const main = document.querySelector(`tr[data-id="${id}"]`);
                            const edit = document.querySelector(
                                `tr.edit-row[data-id="edit-${id}"]`);
                            if (main) main.remove();
                            if (edit) edit.remove();
                            milestonesBody.insertAdjacentHTML('beforeend', json.html);
                        }
                        // hide modal
                        const modalEl = document.getElementById('attainModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }).catch(() => alert('Update failed'));
                }
            });
        });
    </script>
@endpush
