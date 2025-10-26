@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h4>Create PIP</h4>
            <form method="POST" action="{{ route('pips.store') }}">@csrf
                <div class="mb-2">
                    <label>User</label>
                    <select name="user_id" class="form-control">
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2"><label>Reason</label><input name="reason" class="form-control" required></div>
                <div class="mb-2"><label>Start Date</label><input type="date" name="start_date" class="form-control"
                        required></div>
                <div class="mb-2"><label>End Date</label><input type="date" name="end_date" class="form-control"
                        required></div>
                <div class="mb-2"><label>Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
                <button class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>
@endsection
