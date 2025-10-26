@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>{{ $objective ? 'Edit' : 'Add' }} Objective for {{ $employee->name }}</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ $objective ? route('users.objectives.update', ['user_id' => $employee->id, 'objective' => $objective->id]) : route('users.objectives.store', ['user_id' => $employee->id]) }}"
            method="post">
            @csrf
            @if ($objective)
                @method('PUT')
            @endif
            <div class="mb-3">
                <label for="financial_year" class="form-label">Financial Year</label>
                <select name="financial_year" id="financial_year" class="form-control w-auto">
                    @foreach ($years as $y)
                        <option value="{{ $y }}"
                            {{ ($objective ? $objective->financial_year : $financialYear) === $y ? 'selected' : '' }}>
                            {{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3" required>{{ old('description', $objective->description ?? '') }}</textarea>
            </div>
            <div class="mb-3">
                <label for="target" class="form-label">Target</label>
                <input type="text" name="target" id="target" class="form-control"
                    value="{{ old('target', $objective->target ?? '') }}" required />
            </div>
            <div class="mb-3">
                <label for="weightage" class="form-label">Weightage</label>
                <select name="weightage" id="weightage" class="form-control w-auto" required>
                    @foreach ([10, 15, 20, 25] as $w)
                        <option value="{{ $w }}"
                            {{ (int) old('weightage', $objective->weightage ?? 10) === $w ? 'selected' : '' }}>
                            {{ $w }}%</option>
                    @endforeach
                </select>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input is-smart-checkbox" type="checkbox" name="is_smart" id="is_smart"
                    value="1" {{ old('is_smart', $objective->is_smart ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_smart">Is SMART</label>
            </div>
            @include('appraisal.partials.smart_help')
            <div>
                <button type="submit" class="btn btn-primary">{{ $objective ? 'Update' : 'Create' }}</button>
                <a href="{{ route('users.objectives.index', ['user_id' => $employee->id, 'fy' => $financialYear]) }}"
                    class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('change', function(e) {
                var el = e.target;
                if (!el.matches('input[type="checkbox"].is-smart-checkbox')) return;
                // find the nearest help element in the form
                var help = el.closest('form').querySelector('.is-smart-help');
                if (help) help.classList.toggle('d-none', !el.checked);
            });
            // initially reveal if checked
            document.addEventListener('DOMContentLoaded', function() {
                var el = document.querySelector('input[type="checkbox"]#is_smart');
                if (el) {
                    var help = el.closest('form').querySelector('.is-smart-help');
                    if (help) help.classList.toggle('d-none', !el.checked);
                }
            });
        </script>
    @endpush
@endsection
