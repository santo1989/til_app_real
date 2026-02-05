@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="mb-0">Midterm Review (Progress till Midterm)</h5>
                    <small class="text-danger">STRICTLY CONFIDENTIAL WHEN COMPLETED</small>
                </div>
                <x-ui.button variant="danger"
                    href="{{ route('appraisals.midterm.pdf', ['appraisal_id' => $appraisal->id ?? 0]) }}" target="_blank"
                    class="btn-sm">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </x-ui.button>
            </div>
            <form method="POST" action="{{ route('appraisals.midterm.submit') }}">
                @csrf
                @include('components.alert')
                <table class="table">
                    <thead>
                        <tr>
                            <th>KRA</th>
                            <th>Progress %</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($objectives as $i => $obj)
                            <tr>
                                <td>{{ $obj->description }}</td>
                                <td><input type="number" name="achievements[{{ $i }}][score]"
                                        class="form-control" required min="0" max="100" /></td>
                                <td><input type="text" name="achievements[{{ $i }}][comment]"
                                        class="form-control" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <textarea name="comments" class="form-control" placeholder="Overall comments"></textarea>
                <x-ui.button variant="primary" class="mt-2">Submit Midterm</x-ui.button>
            </form>
        </div>
    </div>
@endsection
