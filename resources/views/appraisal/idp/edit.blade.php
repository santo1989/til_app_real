@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Edit IDP</h5>
            <form method="POST" action="{{ route('idp.update', $idp) }}">
                @csrf
                @method('PUT')
                @include('components.alert')

                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" required>{{ old('description', $idp->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label>Review Date</label>
                    <input type="date" name="review_date" class="form-control"
                        value="{{ old('review_date', $idp->review_date) }}" required />
                </div>

                <div class="mb-3">
                    <label>Progress till December</label>
                    <textarea name="progress_till_dec" class="form-control">{{ old('progress_till_dec', $idp->progress_till_dec) }}</textarea>
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="pending" {{ old('status', $idp->status) == 'pending' ? 'selected' : '' }}>Pending
                        </option>
                        <option value="in_progress" {{ old('status', $idp->status) == 'in_progress' ? 'selected' : '' }}>In
                            Progress</option>
                        <option value="completed" {{ old('status', $idp->status) == 'completed' ? 'selected' : '' }}>
                            Completed</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-outline-primary">Update IDP</button>
                <a href="{{ route('idp.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
            @include('appraisal.idp.partials.milestones', ['idp' => $idp])
        </div>
    </div>
@endsection
