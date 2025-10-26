@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Conduct Year-End Review for {{ $employee->name }}</h5>
            <form method="POST" action="{{ route('appraisals.conduct_yearend.submit', $employee->id) }}">@csrf
                <table class="table">
                    <thead>
                        <tr>
                            <th>KRA</th>
                            <th>Achievement %</th>
                            <th>Manager Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($objectives as $i => $obj)
                            <tr>
                                <td>{{ $obj->description }}</td>
                                <td><input type="number" name="achievements[{{ $i }}][score]" class="form-control"
                                        required min="0" max="100" /></td>
                                <td><input type="number" name="achievements[{{ $i }}][rating]"
                                        class="form-control" required min="0" max="100" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button class="btn btn-primary">Submit Year-End Review</button>
            </form>
        </div>
    </div>
@endsection
