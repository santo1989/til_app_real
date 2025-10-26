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
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3" required>{{ old('description', $objective->description ?? '') }}</textarea>
            </div>

            <div class="mb-3">
                <label for="weightage" class="form-label">Weightage %</label>
                <select name="weightage" id="weightage" class="form-control" required>
                    @foreach ([10, 15, 20, 25, 30] as $w)
                        <option value="{{ $w }}"
                            {{ old('weightage', $objective->weightage ?? '') == $w ? 'selected' : '' }}>{{ $w }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Team objectives typically total 30%</small>
            </div>

            <div class="mb-3">
                <label for="target" class="form-label">Target</label>
                <input type="text" name="target" id="target" class="form-control"
                    value="{{ old('target', $objective->target ?? '') }}" required>
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
                <label class="form-check-label" for="is_smart">
                    Is SMART objective?
                </label>
            </div>
            @include('appraisal.partials.smart_help')
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

            <button type="submit" class="btn btn-success">{{ isset($objective) ? 'Update' : 'Create' }}</button>
            <a href="{{ route('team.objectives.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
