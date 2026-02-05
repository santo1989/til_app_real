@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h4>Edit PIP #{{ $pip->id }}</h4>
            <form method="POST" action="{{ route('pips.update', $pip->id) }}">@csrf
                @method('PUT')
                <div class="mb-2">
                    <label>User</label>
                    <select name="user_id" class="form-control">
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ $u->id == $pip->user_id ? 'selected' : '' }}>
                                {{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2"><label>Reason</label><input name="reason" class="form-control"
                        value="{{ $pip->reason }}" required></div>
                <div class="mb-2"><label>Start Date</label><input type="date" name="start_date" class="form-control"
                        value="{{ $pip->start_date }}" required></div>
                <div class="mb-2"><label>End Date</label><input type="date" name="end_date" class="form-control"
                        value="{{ $pip->end_date }}" required></div>
                <div class="mb-2"><label>Status</label>
                    <select name="status" class="form-control">
                        <option value="open" {{ $pip->status == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ $pip->status == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="mb-2"><label>Notes</label>
                    <textarea name="notes" class="form-control">{{ $pip->notes }}</textarea>
                </div>
                <x-ui.button variant="primary" type="submit">Save</x-ui.button>
            </form>
        </div>
    </div>
@endsection
