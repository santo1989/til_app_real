@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5>My Objectives </h5>
            <form method="POST" action="{{ route('objectives.submit') }}">
                @csrf
                @include('components.alert')
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Weightage</th>
                            <th>Target</th>
                            <th>SMART?</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody id="objectives-body">
                        @forelse($objectives as $i => $obj)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <input type="hidden" name="objectives[{{ $i }}][type]" value="individual" />
                                    <input type="text" name="objectives[{{ $i }}][description]"
                                        value="{{ $obj->description }}" class="form-control" required />
                                </td>
                                <td>
                                    <select name="objectives[{{ $i }}][weightage]" class="form-control" required>
                                        @foreach ([10, 15, 20, 25] as $w)
                                            <option value="{{ $w }}"
                                                @if ($obj->weightage == $w) selected @endif>{{ $w }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="objectives[{{ $i }}][target]"
                                        value="{{ $obj->target }}" class="form-control" required /></td>
                                <td><input class="is-smart-checkbox" type="checkbox"
                                        name="objectives[{{ $i }}][is_smart]" value="1"
                                        @if ($obj->is_smart) checked @endif /></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No objectives found. Add below.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <button type="button" id="add-row" class="btn btn-sm btn-outline-secondary">Add Objective</button>
                <button type="submit" id="save-btn" class="btn btn-outline-primary" disabled>Save</button>
            </form>
            @include('appraisal.partials.smart_help', ['id' => 'is-smart-global-help'])
            <div class="mt-2">Total objectives allowed: 3–6. Weightages must sum to 70%.</div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $ === 'undefined') {
                alert('jQuery is not loaded. Please contact admin.');
                return;
            }
            let idx = {{ count($objectives) }};

            function updateValidation() {
                const rows = $('#objectives-body tr');
                let total = 0;
                let valid = true;
                rows.each(function() {
                    const weight = parseInt($(this).find('select[name*="weightage"]').val(), 10);
                    if (!isNaN(weight)) total += weight;
                });
                // enforce count 3..6
                if (rows.length < 3 || rows.length > 6) valid = false;
                // enforce total 70 for individual-only page
                if (total !== 70) valid = false;
                $('#save-btn').prop('disabled', !valid);

                // disable Add when we've reached max count OR total weight already equals/exceeds 70
                const disableAdd = rows.length >= 6 || total >= 70;
                $('#add-row').prop('disabled', disableAdd);

                // toggle global help if any SMART checkbox is checked
                const anySmart = $('#objectives-body').find('input.is-smart-checkbox:checked').length > 0;
                $('#is-smart-global-help').toggleClass('d-none', !anySmart);
            }

            $('#add-row').on('click', function() {
                // prevent adding if disabled (double-safety)
                if ($(this).prop('disabled')) return;

                $('#objectives-body').append(`
                <tr>
                <td>${idx+1}</td>
                <td>
                    <input type="hidden" name="objectives[${idx}][type]" value="individual" />
                    <input type="text" name="objectives[${idx}][description]" class="form-control" required />
                </td>
                <td>
                    <select name="objectives[${idx}][weightage]" class="form-control" required>
                        <option value="">Select</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="25">25</option>
                    </select>
                </td>
                <td><input type="text" name="objectives[${idx}][target]" class="form-control" required /></td>
                <td><input class="is-smart-checkbox" type="checkbox" name="objectives[${idx}][is_smart]" value="1" /></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button></td>
            </tr>
        `);
                idx++;
                updateValidation();
            });

            $('#objectives-body').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                updateValidation();
            });

            $('#objectives-body').on('change', 'select[name*="weightage"]', updateValidation);
            $('#objectives-body').on('input', 'input', updateValidation);

            // also toggle help when SMART checkboxes change
            $('#objectives-body').on('change', 'input.is-smart-checkbox', function() {
                updateValidation();
            });

            updateValidation();
        });
    </script>
@endsection
