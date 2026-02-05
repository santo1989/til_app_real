@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Individual Development Plans (IDP)</h5>
            <form method="POST" action="{{ route('idp.store') }}">@csrf
                <div class="mb-3"><label>Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="mb-3"><label>Review Date</label><input type="date" name="review_date" class="form-control" />
                </div>
                <x-ui.button variant="primary" type="submit">Save IDP</x-ui.button>
            </form>
            <hr>
            <h6>Your IDPs</h6>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Progress</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($idps as $i => $idp)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $idp->description }}</td>
                            <td>{{ $idp->progress_till_dec }}</td>
                            <td><x-ui.button variant="secondary" href="#" class="btn-sm">Edit</x-ui.button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
