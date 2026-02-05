@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>{{ $mode === 'edit' ? 'Edit' : 'Add' }} Audit Log</h3>
        <form method="POST" action="{{ $mode === 'edit' ? route('audit-logs.update', $log) : route('audit-logs.store') }}">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif
            <div class="mb-3">
                <label for="user_id" class="form-label">User</label>
                <select name="user_id" id="user_id" class="form-control" required>
                    <option value="">Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}"
                            {{ old('user_id', $log->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="action" class="form-label">Action</label>
                <input type="text" name="action" id="action" class="form-control"
                    value="{{ old('action', $log->action) }}" required maxlength="255">
            </div>
            <div class="mb-3">
                <label for="details" class="form-label">Details</label>
                <textarea name="details" id="details" class="form-control" rows="4">{{ old('details', $log->details) }}</textarea>
            </div>
            <x-ui.button variant="success" type="submit">{{ $mode === 'edit' ? 'Update' : 'Create' }}</x-ui.button>
            <x-ui.button variant="secondary" href="{{ route('audit-logs.index') }}">Cancel</x-ui.button>
        </form>
    </div>
@endsection
