@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>{{ isset($objective) ? 'Edit' : 'Create' }} Team Objective</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
            action="{{ isset($objective) ? route('team.objectives.update', $objective) : route('team.objectives.store') }}">
            @csrf
            @if (isset($objective))
                @method('PUT')
            @endif
            <div id="objectives-wrapper">
                <div class="mb-3">
                    <label for="department_id" class="form-label">Department</label>
                    <select name="department_id" id="department_id" class="form-control" required>
                        <option value="">Select Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ old('department_id', $objective->department_id ?? '') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Objectives</label>
                    <div id="objective-rows">
                        @php
                            $oldObjectives = old('objectives', []);
                            if (isset($objective) && empty($oldObjectives)) {
                                $oldObjectives = [
                                    [
                                        'description' => $objective->description,
                                        'weightage' => $objective->weightage,
                                        'target' => $objective->target,
                                    ],
                                ];
                            }
                        @endphp
                        @if (!empty($oldObjectives))
                            @foreach ($oldObjectives as $row)
                                <div class="objective-row mb-2">
                                    <input type="text" name="objectives[][description]" class="form-control mb-1"
                                        placeholder="Description" value="{{ $row['description'] ?? '' }}" required />
                                    <div class="d-flex gap-2">
                                        <select name="objectives[][weightage]" class="form-control w-25" required>
                                            @foreach ([10, 15, 20, 25, 30] as $w)
                                                <option value="{{ $w }}"
                                                    {{ ($row['weightage'] ?? '') == $w ? 'selected' : '' }}>
                                                    {{ $w }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="objectives[][target]" class="form-control"
                                            placeholder="Target" value="{{ $row['target'] ?? '' }}" required />
                                        <x-ui.button variant="danger" type="button"
                                            class="btn-sm remove-row">Remove</x-ui.button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="objective-row mb-2">
                                <input type="text" name="objectives[][description]" class="form-control mb-1"
                                    placeholder="Description" required />
                                <div class="d-flex gap-2">
                                    <select name="objectives[][weightage]" class="form-control w-25" required>
                                        @foreach ([10, 15, 20, 25, 30] as $w)
                                            <option value="{{ $w }}">{{ $w }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="objectives[][target]" class="form-control"
                                        placeholder="Target" required />
                                    <x-ui.button variant="danger" type="button"
                                        class="btn-sm remove-row">Remove</x-ui.button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="mt-2">
                        <button type="button" id="add-objective" class="btn btn-outline-primary btn-sm">Add
                            Objective</button>
                        <small class="text-muted ms-2">Provide 2-3 departmental objectives; total weightage must equal
                            30%.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="financial_year" class="form-label">Financial Year</label>
                    <select name="financial_year" id="financial_year" class="form-control" required>
                        @foreach ($years as $y)
                            <option value="{{ $y }}"
                                {{ old('financial_year', $objective->financial_year ?? '2025-26') == $y ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_smart" id="is_smart" class="form-check-input is-smart-checkbox"
                        value="1" {{ old('is_smart', $objective->is_smart ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_smart">Is SMART objective?</label>
                </div>
                @include('appraisal.partials.smart_help')
            </div>
            @push('scripts')
                <script>
                    document.addEventListener('change', function(e) {
                        var el = e.target;
                        if (!el.matches('input[type="checkbox"].is-smart-checkbox')) return;
                        var help = el.closest('form').querySelector('.is-smart-help');
                        if (help) help.classList.toggle('d-none', !el.checked);
                    });
                    document.addEventListener('DOMContentLoaded', function() {
                        var el = document.querySelector('input[type="checkbox"]#is_smart');
                        if (el) {
                            var help = el.closest('form').querySelector('.is-smart-help');
                            if (help) help.classList.toggle('d-none', !el.checked);
                        }
                    });
                </script>
            @endpush

            <x-ui.button variant="success" type="submit">{{ isset($objective) ? 'Update' : 'Create' }}</x-ui.button>
            <x-ui.button variant="secondary" href="{{ route('team.objectives.index') }}">Cancel</x-ui.button>
        </form>
        <script>
            (function() {
                const maxRows = 3;
                const minRows = 2;
                const container = document.getElementById('objective-rows');
                document.getElementById('add-objective').addEventListener('click', function() {
                    const rows = container.querySelectorAll('.objective-row');
                    if (rows.length >= maxRows) return alert('Maximum ' + maxRows + ' objectives allowed');
                    const el = rows[0].cloneNode(true);
                    el.querySelectorAll('input').forEach(i => i.value = '');
                    container.appendChild(el);
                });
                container.addEventListener('click', function(e) {
                    if (!e.target.matches('.remove-row')) return;
                    const rows = container.querySelectorAll('.objective-row');
                    if (rows.length <= 1) return; // keep at least one in UI
                    e.target.closest('.objective-row').remove();
                });
            })();
        </script>
    </div>
@endsection
