@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>IDP Details #{{ $idp->id }}</h4>
                        <div>
                            <x-ui.button variant="warning" href="{{ route('idps.edit', $idp) }}"
                                class="btn-sm">Edit</x-ui.button>
                            <x-ui.button variant="secondary" href="{{ route('idps.index') }}" class="btn-sm">Back to
                                List</x-ui.button>
                            <form action="{{ route('idps.destroy', $idp) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Are you sure you want to delete this IDP?');">
                                @csrf
                                @method('DELETE')
                                <x-ui.button variant="danger" type="submit" class="btn-sm">Delete</x-ui.button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">ID</th>
                                <td>{{ $idp->id }}</td>
                            </tr>
                            <tr>
                                <th>User</th>
                                <td>{{ $idp->user->name ?? 'N/A' }} ({{ $idp->user->email ?? 'N/A' }})</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ $idp->description }}</td>
                            </tr>
                            <tr>
                                <th>Review Date</th>
                                <td>{{ $idp->review_date ? $idp->review_date->format('Y-m-d') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Progress Till December</th>
                                <td>{{ $idp->progress_till_dec ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Revised Description</th>
                                <td>{{ $idp->revised_description ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Accomplishment</th>
                                <td>{{ $idp->accomplishment ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if ($idp->status)
                                        @php
                                            $badgeClass = match ($idp->status) {
                                                'completed' => 'bg-success',
                                                'in_progress' => 'bg-primary',
                                                'pending' => 'bg-warning',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span
                                            class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $idp->status)) }}</span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $idp->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ $idp->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>
                        @include('appraisal.idp.partials.milestones', ['idp' => $idp])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
