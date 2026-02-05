@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Midterm Review (Progress)</h5>
            <form method="POST" action="{{ route('appraisals.midterm.submit') }}">@csrf
                <table class="table">
                    <thead>
                        <tr>
                            <th>KRA</th>
                            <th>Progress %</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($objectives as $obj)
                            <tr>
                                <td>{{ $obj->description }}</td>
                                <td><input type="number" name="achievements[][score]" class="form-control" /></td>
                                <td><input type="text" name="achievements[][comment]" class="form-control" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <textarea name="comments" class="form-control" placeholder="Overall comments"></textarea>
                <button class="btn btn-outline-primary mt-2">Submit Midterm</button>
            </form>
        </div>
    </div>
@endsection
