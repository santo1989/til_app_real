@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Set Departmental Objectives</h5>
            <form method="POST" action="{{ route('objectives.board.set') }}">@csrf
                <table class="table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Objective</th>
                            <th>Weightage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $dept)
                            <tr>
                                <td>{{ $dept->name }}</td>
                                <td><input type="text" name="objectives[{{ $dept->id }}][description]"
                                        class="form-control" /></td>
                                <td><input type="number" name="objectives[{{ $dept->id }}][weightage]"
                                        class="form-control" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button class="btn btn-primary">Set Objectives</button>
            </form>
        </div>
    </div>
@endsection
