@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Individual Development Plans (IDP)</h5>
            <form method="POST" action="{{ route('idp.store') }}">
                @csrf
                @include('components.alert')
                <div class="mb-3"><label>Description</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>
                <div class="mb-3"><label>Review Date</label><input type="date" name="review_date" class="form-control"
                        required />
                </div>
                <button class="btn btn-outline-primary">Save IDP</button>
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
                            <td>{{ $idp->progress_till_dec ?? 'N/A' }}</td>
                            <td><x-ui.button variant="secondary" href="{{ route('idp.edit', $idp) }}"
                                    class="btn-sm">Edit</x-ui.button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
