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
                            <th>Weightage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($objectives as $i => $obj)
                            <tr>
                                <td>{{ $obj->description }}</td>
                                <td>
                                    <input type="hidden" name="achievements[{{ $i }}][id]"
                                        value="{{ $obj->id }}" />
                                    <input type="number" name="achievements[{{ $i }}][score]" class="form-control"
                                        required min="0" max="100" />
                                </td>
                                <td><input type="number" name="achievements[{{ $i }}][rating]"
                                        class="form-control" required min="0" max="100" /></td>
                                <td>{{ $obj->weightage }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mb-3">
                    <label for="supervisor_comments" class="form-label">Supervisor Comments</label>
                    <textarea id="supervisor_comments" name="supervisor_comments" class="form-control" rows="3"></textarea>
                </div>
                <x-ui.button variant="primary">Submit Year-End Review</x-ui.button>
            </form>
        </div>
    </div>
@endsection
