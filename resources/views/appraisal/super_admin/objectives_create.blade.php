@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Create Objective</h4>
                        <x-ui.button variant="secondary" href="{{ route('objectives.index') }}">Back to List</x-ui.button>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('objectives.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id"
                                    class="form-control @error('user_id') is-invalid @enderror" required>
                                    <option value="">Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department (Optional)</label>
                                <select name="department_id" id="department_id"
                                    class="form-control @error('department_id') is-invalid @enderror">
                                    <option value="">None</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type"
                                    class="form-control @error('type') is-invalid @enderror" required>
                                    <option value="individual" {{ old('type') == 'individual' ? 'selected' : '' }}>
                                        Individual</option>
                                    <option value="departmental" {{ old('type') == 'departmental' ? 'selected' : '' }}>
                                        Departmental</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span
                                        class="text-danger">*</span></label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="3" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="weightage" class="form-label">Weightage (%) <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="weightage" id="weightage"
                                    class="form-control @error('weightage') is-invalid @enderror"
                                    value="{{ old('weightage') }}" min="10" max="30" required>
                                @error('weightage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="target" class="form-label">Target <span class="text-danger">*</span></label>
                                <textarea name="target" id="target" class="form-control @error('target') is-invalid @enderror" rows="2"
                                    required>{{ old('target') }}</textarea>
                                @error('target')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="financial_year" class="form-label">Financial Year <span
                                        class="text-danger">*</span></label>
                                <select name="financial_year" id="financial_year"
                                    class="form-control @error('financial_year') is-invalid @enderror" required>
                                    <option value="">Select Year</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}"
                                            {{ old('financial_year', '2025-26') == $year ? 'selected' : '' }}>
                                            {{ $year }}</option>
                                    @endforeach
                                </select>
                                @error('financial_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status"
                                    class="form-control @error('status') is-invalid @enderror">
                                    <option value="set" {{ old('status') == 'set' ? 'selected' : '' }}>Set</option>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check mb-2">
                                <input type="checkbox" name="is_smart" id="is_smart"
                                    class="form-check-input is-smart-checkbox" value="1"
                                    {{ old('is_smart') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_smart">
                                    SMART Objective
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

                            <div class="d-flex gap-2">
                                <x-ui.button variant="primary" type="submit">Create Objective</x-ui.button>
                                <x-ui.button variant="secondary"
                                    href="{{ route('objectives.index') }}">Cancel</x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
