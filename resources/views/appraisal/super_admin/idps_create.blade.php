@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Create IDP (Individual Development Plan)</h4>
                        <x-ui.button variant="secondary" href="{{ route('idps.index') }}">Back to List</x-ui.button>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('idps.store') }}">
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
                                <label for="description" class="form-label">Description <span
                                        class="text-danger">*</span></label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="4" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="review_date" class="form-label">Review Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="review_date" id="review_date"
                                    class="form-control @error('review_date') is-invalid @enderror"
                                    value="{{ old('review_date') }}" required>
                                @error('review_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="progress_till_dec" class="form-label">Progress Till December</label>
                                <textarea name="progress_till_dec" id="progress_till_dec"
                                    class="form-control @error('progress_till_dec') is-invalid @enderror" rows="3">{{ old('progress_till_dec') }}</textarea>
                                @error('progress_till_dec')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="revised_description" class="form-label">Revised Description</label>
                                <textarea name="revised_description" id="revised_description"
                                    class="form-control @error('revised_description') is-invalid @enderror" rows="3">{{ old('revised_description') }}</textarea>
                                @error('revised_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="accomplishment" class="form-label">Accomplishment</label>
                                <textarea name="accomplishment" id="accomplishment" class="form-control @error('accomplishment') is-invalid @enderror"
                                    rows="3">{{ old('accomplishment') }}</textarea>
                                @error('accomplishment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status"
                                    class="form-control @error('status') is-invalid @enderror">
                                    <option value="">Not Set</option>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In
                                        Progress</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <x-ui.button variant="primary" type="submit">Create IDP</x-ui.button>
                                <x-ui.button variant="secondary" href="{{ route('idps.index') }}">Cancel</x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
