@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>My Objectives ({{ $financial_year ?? 'Current' }})</h5>
            <form method="POST" action="{{ route('objectives.submit') }}">@csrf
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Weightage</th>
                            <th>Target</th>
                        </tr>
                    </thead>
                    <tbody id="objectives-body">
                        @forelse($objectives as $i => $obj)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <input type="hidden" name="objectives[{{ $i }}][type]" value="individual" />
                                    <input type="text" name="objectives[{{ $i }}][description]"
                                        value="{{ $obj->description }}" class="form-control" />
                                </td>
                                <td><input type="number" name="objectives[{{ $i }}][weightage]"
                                        value="{{ $obj->weightage }}" class="form-control" /></td>
                                <td><input type="text" name="objectives[{{ $i }}][target]"
                                        value="{{ $obj->target }}" class="form-control" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No objectives found. Add below.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <button type="button" id="add-row" class="btn btn-sm btn-secondary">Add Objective</button>
                <button type="submit" id="save-btn" class="btn btn-primary" disabled>Save</button>
            </form>
        </div>
    </div>
    <script>
        $(function() {
            let idx = {{ count($objectives) }} || 0;

            function updateValidation() {
                const rows = $('#objectives-body tr');
                let total = 0;
                rows.each(function() {
                    const val = $(this).find('input[name*="weightage"]').val();
                    const weight = parseInt(val, 10);
                    if (!isNaN(weight)) total += weight;
                });
                let valid = true;
                if (rows.length < 3 || rows.length > 6) valid = false;
                if (total !== 100) valid = false;
                $('#save-btn').prop('disabled', !valid);
                $('#add-row').prop('disabled', rows.length >= 6);
                // show a small inline helper if invalid
                if (!valid) {
                    if ($('#objectives-validation-msg').length === 0) {
                        $('#objectives-body').closest('table').after(
                            '<div id="objectives-validation-msg" class="text-danger mt-2">Objectives must be 3–6 items and weightages must sum to 100%.</div>'
                            );
                    }
                } else {
                    $('#objectives-validation-msg').remove();
                }
            }

            $('#add-row').on('click', function() {
                $('#objectives-body').append(`\
            <tr>\
                <td>${idx+1}</td>\
                <td>\
                        <input type="hidden" name="objectives[${idx}][type]" value="individual" />\
                        <input type="text" name="objectives[${idx}][description]" class="form-control"/>\
                </td>\
                <td><input type="number" name="objectives[${idx}][weightage]" class="form-control" min="0" max="100"/></td>\
                <td><input type="text" name="objectives[${idx}][target]" class="form-control"/></td>\
            </tr>\
        `);
                idx++;
                updateValidation();
            });

            $('#objectives-body').on('input change', 'input[name*="weightage"]', updateValidation);
            $('#objectives-body').on('input', 'input[name*="description"], input[name*="target"]',
            updateValidation);

            // allow removal if future UI adds remove buttons; keep handler ready
            $('#objectives-body').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                updateValidation();
            });

            updateValidation();
        });
    </script>
@endsection
