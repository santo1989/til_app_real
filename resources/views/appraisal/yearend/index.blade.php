@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Year-End Self Assessment</h5>
            <div><small class="text-danger">STRICTLY CONFIDENTIAL WHEN COMPLETED</small></div>
            <form method="POST" action="{{ route('appraisals.yearend.submit') }}">
                @csrf
                @include('components.alert')
                <table class="table">
                    <thead>
                        <tr>
                            <th>KRA</th>
                            <th>Achievement %</th>
                            <th>Weightage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($objectives as $obj)
                            <tr>
                                <td>{{ $obj->description }}</td>
                                <td><input type="number" name="achievements[][score]" class="form-control" required
                                        min="0" max="100" /></td>
                                <td><input type="number" name="achievements[][weight]" value="{{ $obj->weightage }}"
                                        class="form-control" required min="10" max="25" step="5" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div>Total (auto-calculated): <span id="total">0</span></div>
                <textarea name="comments" class="form-control" placeholder="Comments"></textarea>
                <x-ui.button variant="primary" class="mt-2">Submit Year-End</x-ui.button>
            </form>
        </div>
    </div>
    <script>
        $('input[name="achievements[][score]"]').on('input', function() {
            let total = 0;
            $('table tbody tr').each(function() {
                const score = Number($(this).find('input[name="achievements[][score]"]').val()) || 0;
                const weight = Number($(this).find('input[name="achievements[][weight]"]').val()) || 0;
                total += (score * weight) / 100;
            });
            $('#total').text(total.toFixed(2));
        });
    </script>
@endsection
