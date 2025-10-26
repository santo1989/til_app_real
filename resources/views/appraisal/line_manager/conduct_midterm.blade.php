@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Conduct Midterm Review for {{ $employee->name }}</h5>
                <a href="{{ route('appraisals.midterm.pdf', ['appraisal_id' => $appraisal->id ?? 0]) }}" target="_blank"
                    class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                @if (isset($appraisal) && $appraisal->id)
                    <div class="ml-2">
                        <label class="mr-2">Employee Sign:</label>
                        @php $role = 'employee'; @endphp
                        @include('appraisal.partials.signature_form', [
                            'appraisal' => $appraisal,
                            'role' => $role,
                        ])
                    </div>
                    <div class="ml-2">
                        <label class="mr-2">Manager Sign:</label>
                        @php $role = 'manager'; @endphp
                        @include('appraisal.partials.signature_form', [
                            'appraisal' => $appraisal,
                            'role' => $role,
                        ])
                    </div>
                @endif
            </div>
            <form method="POST" action="{{ route('appraisals.conduct_midterm.submit', $employee->id) }}">@csrf
                <table class="table">
                    <thead>
                        <tr>
                            <th>KRA</th>
                            <th>Progress %</th>
                            <th>Manager Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($objectives as $i => $obj)
                            <tr>
                                <td>{{ $obj->description }}</td>
                                <td>
                                    <input type="hidden" name="reviews[{{ $i }}][id]"
                                        value="{{ $obj->id }}">
                                    <input type="number" name="reviews[{{ $i }}][score]" class="form-control"
                                        required min="0" max="100" />
                                </td>
                                <td><input type="text" name="reviews[{{ $i }}][comment]"
                                        class="form-control" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button class="btn btn-primary">Submit Midterm Review</button>
            </form>
        </div>
    </div>
@endsection
