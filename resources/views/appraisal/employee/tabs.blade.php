@extends('layouts.app')
@section('content')
    <style>
        /* Example-inspired styles (Tosrifa colors) */
        .header-block {
            background-color: #1a6b3c;
            color: #fff;
            padding: 18px;
            border-radius: 6px;
            margin-bottom: 18px;
        }

        .header-block .logo {
            width: 56px;
            margin-right: 12px;
        }

        .form-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.06);
            padding: 20px;
        }

        .section-title {
            color: #1a6b3b;
            border-bottom: 2px solid #1a6b3b;
            padding-bottom: 8px;
            margin-bottom: 18px;
        }

        .signature-pad {
            border: 1px dashed #ccc;
            background: #fafafa;
            min-height: 80px;
        }

        .table th {
            background: #e9f5ee;
            color: #1a6b3b;
        }

        .btn-primary {
            background: #1a6b3b;
            border-color: #1a6b3b;
        }

        .btn-primary:hover {
            background: #14532d;
            border-color: #14532d;
        }
    </style>

    <div class="container my-4">
        <div class="header-block text-center d-flex align-items-center justify-content-center">
            <img src="{{ asset('images/logo.png') }}" alt="TIL" class="logo" onerror="this.style.display='none'">
            <h2 class="mb-0">Tosrifa Industries Limited — Performance Appraisal</h2>
        </div>

        <ul class="nav nav-tabs mb-3" id="appraisalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="objective-tab" data-bs-toggle="tab" data-bs-target="#objective"
                    type="button" role="tab">Objective Setting</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="midterm-tab" data-bs-toggle="tab" data-bs-target="#midterm" type="button"
                    role="tab">Midterm Appraisal</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="yearend-tab" data-bs-toggle="tab" data-bs-target="#yearend" type="button"
                    role="tab">Year End Appraisal</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="objective" role="tabpanel">
                <div class="form-container">
                    <h3 class="section-title">Objective Setting — {{ $financial_year ?? 'Current' }}</h3>
                    <form method="POST" action="{{ route('objectives.submit') }}">@csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-2"><label class="form-label">Name</label><input class="form-control"
                                        value="{{ auth()->user()->name }}" readonly></div>
                                <div class="mb-2"><label class="form-label">Designation</label><input class="form-control"
                                        value="{{ auth()->user()->designation ?? '' }}" readonly></div>
                                <div class="mb-2"><label class="form-label">Date of Joining</label><input
                                        class="form-control"
                                        value="{{ optional(auth()->user()->date_of_joining)->format('d M, Y') ?? '' }}"
                                        readonly></div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2"><label class="form-label">Employee ID</label><input class="form-control"
                                        value="{{ auth()->user()->employee_id ?? '' }}" readonly></div>
                                <div class="mb-2"><label class="form-label">Department</label><input class="form-control"
                                        value="{{ auth()->user()->department->name ?? '' }}" readonly></div>
                                <div class="mb-2"><label class="form-label">Tenure in current role</label><input
                                        class="form-control" value="{{ auth()->user()->tenure_in_current_role ?? '' }}"
                                        readonly></div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="objective-table">
                                <thead>
                                    <tr>
                                        <th>Sl #</th>
                                        <th>Objectives/Action Plans</th>
                                        <th>Timeline</th>
                                        <th>Weightage %</th>
                                    </tr>
                                </thead>
                                <tbody id="objectives-body">
                                    @forelse($objectives as $i => $obj)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td><input type="hidden" name="objectives[{{ $i }}][type]"
                                                    value="individual"><input type="text"
                                                    name="objectives[{{ $i }}][description]"
                                                    value="{{ $obj->description }}" class="form-control"></td>
                                            <td><input type="text" name="objectives[{{ $i }}][timeline]"
                                                    value="{{ $obj->timeline ?? '' }}" class="form-control"></td>
                                            <td><input type="number" name="objectives[{{ $i }}][weightage]"
                                                    value="{{ $obj->weightage ?? 0 }}" class="form-control weightage"
                                                    min="0" max="100"></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">No objectives found. Add below.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <td colspan="3" class="text-end"><strong>Total Weightage</strong></td>
                                        <td id="total-weightage">0%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" id="add-row" class="btn btn-sm btn-outline-secondary">Add
                                Objective</button>
                            <button type="submit" id="save-objectives" class="btn btn-primary" disabled>Save
                                Objectives</button>
                        </div>

                        <div class="mt-4 row">
                            <div class="col-md-4">
                                <label class="form-label">Signature of the employee</label>
                                <div id="employee-signature-pad-obj" class="signature-pad"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                    onclick="clearSignature('employee-signature-pad-obj')">Clear</button>
                                <input type="hidden" id="employee-signature-obj" name="sign_employee_obj">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Signature of the immediate line manager</label>
                                <div id="manager-signature-pad-obj" class="signature-pad"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                    onclick="clearSignature('manager-signature-pad-obj')">Clear</button>
                                <input type="hidden" id="manager-signature-obj" name="sign_manager_obj">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Signature of HR</label>
                                <div id="hr-signature-pad-obj" class="signature-pad"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                    onclick="clearSignature('hr-signature-pad-obj')">Clear</button>
                                <input type="hidden" id="hr-signature-obj" name="sign_hr_obj">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tab-pane fade" id="midterm" role="tabpanel">
                <div class="form-container">
                    <h3 class="section-title">Midterm Appraisal</h3>
                    <form method="POST" action="{{ route('appraisals.midterm.submit') }}">@csrf
                        <div class="table-responsive">
                            <table class="table" id="midterm-table">
                                <thead>
                                    <tr>
                                        <th>Sl.</th>
                                        <th>Objectives/Action Plans</th>
                                        <th>Timeline</th>
                                        <th>Weightage %</th>
                                        <th>% Target Achieved (TA)</th>
                                        <th>Final Score</th>
                                        <th>Action Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($objectives as $i => $obj)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $obj->description }}</td>
                                            <td>{{ $obj->timeline ?? '' }}</td>
                                            <td><input type="number" class="form-control weightage-mid"
                                                    name="mid[{{ $i }}][weight]"
                                                    value="{{ $obj->weightage ?? 0 }}"></td>
                                            <td><input type="number" class="form-control ta"
                                                    name="mid[{{ $i }}][ta]"></td>
                                            <td class="score-mid">0</td>
                                            <td><input type="text" class="form-control"
                                                    name="mid[{{ $i }}][action]"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <td colspan="5" class="text-end"><strong>Total</strong></td>
                                        <td id="midterm-total-score">0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="mt-3 row">
                            <div class="col-md-4">
                                <label class="form-label">Employee Signature</label>
                                <div id="employee-signature-pad-mid" class="signature-pad"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                    onclick="clearSignature('employee-signature-pad-mid')">Clear</button>
                                <input type="hidden" id="employee-signature-mid" name="sign_employee_mid">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Manager Signature</label>
                                <div id="manager-signature-pad-mid" class="signature-pad"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                    onclick="clearSignature('manager-signature-pad-mid')">Clear</button>
                                <input type="hidden" id="manager-signature-mid" name="sign_manager_mid">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">HR Signature</label>
                                <div id="hr-signature-pad-mid" class="signature-pad"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                    onclick="clearSignature('hr-signature-pad-mid')">Clear</button>
                                <input type="hidden" id="hr-signature-mid" name="sign_hr_mid">
                            </div>
                        </div>
                        <div class="mt-3">
                            <textarea name="comments" class="form-control" placeholder="Overall comments"></textarea>
                            <button class="btn btn-primary mt-2">Submit Midterm</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tab-pane fade" id="yearend" role="tabpanel">
                <div class="form-container">
                    <h3 class="section-title">Year End Appraisal</h3>
                    <form method="POST" action="{{ route('appraisals.yearend.submit') }}">@csrf
                        <div class="table-responsive">
                            <table class="table" id="yearend-table">
                                <thead>
                                    <tr>
                                        <th>Sl.</th>
                                        <th>Objectives/Action Plans</th>
                                        <th>Timeline</th>
                                        <th>Weightage</th>
                                        <th>% Achieved</th>
                                        <th>Final Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($objectives as $i => $obj)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $obj->description }}</td>
                                            <td>{{ $obj->timeline ?? '' }}</td>
                                            <td><input type="number" class="form-control weight-year"
                                                    name="year[{{ $i }}][weight]"
                                                    value="{{ $obj->weightage ?? 0 }}"></td>
                                            <td><input type="number" class="form-control ta-year"
                                                    name="year[{{ $i }}][ta]"></td>
                                            <td class="score-year">0</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <td colspan="5" class="text-end"><strong>Total</strong></td>
                                        <td id="year-total">0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="mt-3">
                            <h5>Performance Rating</h5>
                            <div class="mb-2"><label>Calculated Rating</label><input type="text" id="rating"
                                    class="form-control" readonly></div>
                        </div>
                        <div class="mt-3 row">
                            <div class="col-md-3">
                                <label class="form-label">Employee Signature</label>
                                <div id="employee-signature-pad-year" class="signature-pad"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                    onclick="clearSignature('employee-signature-pad-year')">Clear</button>
                                <input type="hidden" id="employee-signature-year" name="sign_employee_year">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Manager Signature</label>
                                <div id="manager-signature-pad-year" class="signature-pad"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                    onclick="clearSignature('manager-signature-pad-year')">Clear</button>
                                <input type="hidden" id="manager-signature-year" name="sign_manager_year">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Supervisor Signature</label>
                                <div id="supervisor-signature-pad" class="signature-pad"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                    onclick="clearSignature('supervisor-signature-pad')">Clear</button>
                                <input type="hidden" id="supervisor-signature" name="sign_supervisor_year">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">HR Signature</label>
                                <div id="hr-manager-signature-pad-year" class="signature-pad"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                    onclick="clearSignature('hr-manager-signature-pad-year')">Clear</button>
                                <input type="hidden" id="hr-manager-signature-year" name="sign_hr_manager_year">
                            </div>
                        </div>
                        <div class="mt-3"><x-ui.button type="submit" variant="primary">Submit Year-End</x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // signature pad + simple calculations (lightweight, adapted from example)
        document.addEventListener('DOMContentLoaded', function() {
            // initialize signature pads
            const pads = [
                ['employee-signature-pad-obj', 'employee-signature-obj'],
                ['manager-signature-pad-obj', 'manager-signature-obj'],
                ['hr-signature-pad-obj', 'hr-signature-obj'],
                ['employee-signature-pad-mid', 'employee-signature-mid'],
                ['manager-signature-pad-mid', 'manager-signature-mid'],
                ['hr-signature-pad-mid', 'hr-signature-mid'],
                ['employee-signature-pad-year', 'employee-signature-year'],
                ['manager-signature-pad-year', 'manager-signature-year'],
                ['supervisor-signature-pad', 'supervisor-signature'],
                ['hr-manager-signature-pad-year', 'hr-manager-signature-year']
            ];
            pads.forEach(p => initializeSignaturePad(p[0], p[1]));

            // objective add/validation
            let idx = {{ count($objectives) }} || 0;

            function updateObjectiveValidation() {
                let total = 0;
                let rows = document.querySelectorAll('#objectives-body tr');
                rows.forEach(r => {
                    const val = r.querySelector('.weightage')?.value;
                    total += Number(val) || 0;
                });
                document.getElementById('total-weightage').textContent = total + '%';
                const saveBtn = document.getElementById('save-objectives');
                const valid = rows.length >= 3 && rows.length <= 6 && total === 100;
                if (saveBtn) saveBtn.disabled = !valid;
            }
            document.getElementById('add-row').addEventListener('click', function() {
                const tbody = document.getElementById('objectives-body');
                const tr = document.createElement('tr');
                tr.innerHTML =
                    `\n<td>${idx+1}</td>\n<td><input type="hidden" name="objectives[${idx}][type]" value="individual"><input type="text" name="objectives[${idx}][description]" class="form-control"></td>\n<td><input type="text" name="objectives[${idx}][timeline]" class="form-control"></td>\n<td><input type="number" name="objectives[${idx}][weightage]" class="form-control weightage" min="0" max="100"></td>`;
                tbody.appendChild(tr);
                idx++;
                updateObjectiveValidation();
                tr.querySelector('.weightage').addEventListener('input', updateObjectiveValidation);
            });
            document.querySelectorAll('#objectives-body .weightage').forEach(i => i.addEventListener('input',
                updateObjectiveValidation));
            updateObjectiveValidation();

            // midterm calculations
            function calculateMidtermScores() {
                let total = 0;
                document.querySelectorAll('#midterm-table tbody tr').forEach(r => {
                    const w = Number(r.querySelector('.weightage-mid')?.value) || 0;
                    const ta = Number(r.querySelector('.ta')?.value) || 0;
                    const sc = (w * ta / 100).toFixed(2);
                    r.querySelector('.score-mid').textContent = sc;
                    total += Number(sc) || 0;
                });
                document.getElementById('midterm-total-score').textContent = total.toFixed(2);
            }
            document.querySelectorAll('.weightage-mid, .ta').forEach(el => el.addEventListener('input',
                calculateMidtermScores));

            // year calculations
            function calculateYearTotals() {
                let total = 0;
                document.querySelectorAll('#yearend-table tbody tr').forEach(r => {
                    const w = Number(r.querySelector('.weight-year')?.value) || 0;
                    const ta = Number(r.querySelector('.ta-year')?.value) || 0;
                    const sc = (w * ta / 100).toFixed(2);
                    r.querySelector('.score-year').textContent = sc;
                    total += Number(sc) || 0;
                });
                document.getElementById('year-total').textContent = total.toFixed(2);
                const rating = total >= 95 ? 'Outstanding' : total >= 85 ? 'Very Good' : total >= 70 ? 'Good' :
                    'Below';
                document.getElementById('rating').value = rating;
            }
            document.querySelectorAll('.weight-year, .ta-year').forEach(el => el.addEventListener('input',
                calculateYearTotals));
        });

        // Signature pad helper (simple canvas implementation)
        function initializeSignaturePad(padId, hiddenInputId) {
            const wrapper = document.getElementById(padId);
            if (!wrapper) return;
            const canvas = document.createElement('canvas');
            wrapper.appendChild(canvas);

            function resize() {
                canvas.width = wrapper.clientWidth;
                canvas.height = Math.max(80, wrapper.clientHeight);
            }
            resize();
            window.addEventListener('resize', resize);
            const ctx = canvas.getContext('2d');
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 2;
            let drawing = false,
                lastX = 0,
                lastY = 0;
            canvas.addEventListener('mousedown', e => {
                drawing = true;
                lastX = e.offsetX;
                lastY = e.offsetY;
            });
            canvas.addEventListener('mousemove', e => {
                if (!drawing) return;
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(e.offsetX, e.offsetY);
                ctx.stroke();
                lastX = e.offsetX;
                lastY = e.offsetY;
                document.getElementById(hiddenInputId).value = canvas.toDataURL();
            });
            canvas.addEventListener('mouseup', () => drawing = false);
            canvas.addEventListener('mouseout', () => drawing = false);
            // touch
            canvas.addEventListener('touchstart', e => {
                e.preventDefault();
                const t = e.touches[0];
                const rect = canvas.getBoundingClientRect();
                lastX = t.clientX - rect.left;
                lastY = t.clientY - rect.top;
                drawing = true;
            });
            canvas.addEventListener('touchmove', e => {
                e.preventDefault();
                if (!drawing) return;
                const t = e.touches[0];
                const rect = canvas.getBoundingClientRect();
                const x = t.clientX - rect.left;
                const y = t.clientY - rect.top;
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(x, y);
                ctx.stroke();
                lastX = x;
                lastY = y;
                document.getElementById(hiddenInputId).value = canvas.toDataURL();
            });
            canvas.addEventListener('touchend', () => drawing = false);
        }

        function clearSignature(padId) {
            const wrapper = document.getElementById(padId);
            if (!wrapper) return;
            const canvas = wrapper.querySelector('canvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            const hidden = padId.replace('-pad', '');
            const input = document.getElementById(hidden) || document.getElementById(padId.replace('-pad', ''));
            if (input) input.value = '';
        }
    </script>
@endsection
