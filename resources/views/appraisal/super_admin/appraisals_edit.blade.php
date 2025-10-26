@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Edit Appraisal #{{ $appraisal->id }}</h4>
                        <div>
                            <a href="{{ route('appraisals.show', $appraisal) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('appraisals.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('appraisals.update', $appraisal) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id"
                                    class="form-control @error('user_id') is-invalid @enderror" required>
                                    <option value="">Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('user_id', $appraisal->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type"
                                    class="form-control @error('type') is-invalid @enderror" required>
                                    <option value="midterm"
                                        {{ old('type', $appraisal->type) == 'midterm' ? 'selected' : '' }}>Midterm</option>
                                    <option value="year_end"
                                        {{ old('type', $appraisal->type) == 'year_end' ? 'selected' : '' }}>Year End
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="date"
                                    class="form-control @error('date') is-invalid @enderror"
                                    value="{{ old('date', $appraisal->date ? $appraisal->date->format('Y-m-d') : '') }}"
                                    required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="achievement_score" class="form-label">Achievement Score</label>
                                <input type="number" step="0.01" name="achievement_score" id="achievement_score"
                                    class="form-control @error('achievement_score') is-invalid @enderror"
                                    value="{{ old('achievement_score', $appraisal->achievement_score) }}" min="0"
                                    max="100">
                                @error('achievement_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="total_score" class="form-label">Total Score</label>
                                <input type="number" step="0.01" name="total_score" id="total_score"
                                    class="form-control @error('total_score') is-invalid @enderror"
                                    value="{{ old('total_score', $appraisal->total_score) }}" min="0"
                                    max="100">
                                @error('total_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <select name="rating" id="rating"
                                    class="form-control @error('rating') is-invalid @enderror">
                                    <option value="">Not Rated</option>
                                    <option value="Outstanding"
                                        {{ old('rating', $appraisal->rating) == 'Outstanding' ? 'selected' : '' }}>
                                        Outstanding</option>
                                    <option value="Excellent"
                                        {{ old('rating', $appraisal->rating) == 'Excellent' ? 'selected' : '' }}>
                                        Excellent</option>
                                    <option value="Good"
                                        {{ old('rating', $appraisal->rating) == 'Good' ? 'selected' : '' }}>Good</option>
                                    <option value="Average"
                                        {{ old('rating', $appraisal->rating) == 'Average' ? 'selected' : '' }}>Average
                                    </option>
                                    <option value="Below Average"
                                        {{ old('rating', $appraisal->rating) == 'Below Average' ? 'selected' : '' }}>Below
                                        Average
                                    </option>
                                </select>
                                @error('rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="comments" class="form-label">Comments</label>
                                <textarea name="comments" id="comments" class="form-control @error('comments') is-invalid @enderror" rows="4">{{ old('comments', $appraisal->comments) }}</textarea>
                                @error('comments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="financial_year" class="form-label">Financial Year <span
                                        class="text-danger">*</span></label>
                                <select name="financial_year" id="financial_year"
                                    class="form-control @error('financial_year') is-invalid @enderror" required>
                                    <option value="">Select Year</option>
                                    @php
                                        $start = 2025;
                                    @endphp
                                    @for ($i = 0; $i < 11; $i++)
                                        @php $year = ($start + $i) . '-' . substr($start + $i + 1, -2); @endphp
                                        <option value="{{ $year }}"
                                            {{ old('financial_year', $appraisal->financial_year) == $year ? 'selected' : '' }}>
                                            {{ $year }}</option>
                                    @endfor
                                </select>
                                @error('financial_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Update Appraisal</button>
                                <a href="{{ route('appraisals.show', $appraisal) }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
