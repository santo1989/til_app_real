@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Set Objectives for: {{ $employee->name }}</h5>
            <form method="POST" action="{{ route('objectives.set_for_user', $employee->id) }}">
                @csrf
                @include('components.alert')

                <div class="mb-3">
                    <strong>Employee:</strong> {{ $employee->name }}<br>
                    <strong>Department:</strong> {{ $employee->department->name ?? 'N/A' }}<br>
                    <strong>Role:</strong> {{ $employee->role }}
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Weightage (%)</th>
                            <th>Target</th>
                            <th>SMART?</th>
                        </tr>
                    </thead>
                    <tbody id="objectives-body">
                        @forelse($employee->objectives()->where('financial_year', '2025-26')->get() as $i => $obj)
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
                                                {{ $obj->weightage == $w ? 'selected' : '' }}>{{ $w }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="objectives[{{ $i }}][target]"
                                        value="{{ $obj->target }}" class="form-control" required /></td>
                                <td><input class="is-smart-checkbox" type="checkbox"
                                        name="objectives[{{ $i }}][is_smart]" value="1"
                                        {{ $obj->is_smart ? 'checked' : '' }} /></td>
                            </tr>
                        @empty
                            <tr>
                                <td>1</td>
                                <td>
                                    <input type="hidden" name="objectives[0][type]" value="individual" />
                                    <input type="text" name="objectives[0][description]" class="form-control" required />
                                </td>
                                <td>
                                    <select name="objectives[0][weightage]" class="form-control" required>
                                        @foreach ([10, 15, 20, 25] as $w)
                                            <option value="{{ $w }}">{{ $w }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="objectives[0][target]" class="form-control" required /></td>
                                <td><input class="is-smart-checkbox" type="checkbox" name="objectives[0][is_smart]"
                                        value="1" /></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <x-ui.button variant="secondary" type="button" id="add-row" class="btn-sm">Add Objective</x-ui.button>
                <x-ui.button variant="primary" type="submit">Save Objectives</x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('objectives.team') }}">Cancel</x-ui.button>

                <div class="mt-2 text-muted">
                    <small>Total objectives: 2–6 | Weightages must sum to 100% | Allowed: 10%, 15%, 20%, 25%</small>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function() {
            let idx = {{ $employee->objectives()->where('financial_year', '2025-26')->count() }};
            $('#add-row').on('click', function() {
                $('#objectives-body').append(`
                    <tr>
                        <td>${idx + 1}</td>
                        <td>
                            <input type="hidden" name="objectives[${idx}][type]" value="individual" />
                            <input type="text" name="objectives[${idx}][description]" class="form-control" required />
                        </td>
                        <td>
                            <select name="objectives[${idx}][weightage]" class="form-control" required>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="25">25</option>
                            </select>
                        </td>
                        <td><input type="text" name="objectives[${idx}][target]" class="form-control" required /></td>
                        <td><input class="is-smart-checkbox" type="checkbox" name="objectives[${idx}][is_smart]" value="1" /></td>
                    </tr>
                `);
                idx++;
            });
            // global help box toggling
            function toggleSmartHelp() {
                var any = $('#objectives-body').find('input.is-smart-checkbox:checked').length > 0;
                if (any) {
                    $('#is-smart-global-help').removeClass('d-none');
                } else {
                    $('#is-smart-global-help').addClass('d-none');
                }
            }
            $('#objectives-body').on('change', 'input.is-smart-checkbox', toggleSmartHelp);
            // initial
            toggleSmartHelp();
        });
    </script>
    @include('appraisal.partials.smart_help', ['id' => 'is-smart-global-help'])
@endsection
