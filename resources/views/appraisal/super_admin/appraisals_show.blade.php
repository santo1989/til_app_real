@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Appraisal Details #{{ $appraisal->id }}</h4>
                        <div>
                            <a href="{{ route('appraisals.edit', $appraisal) }}" class="btn btn-warning btn-sm">Edit</a>
                            <a href="{{ route('appraisals.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                            <form action="{{ route('appraisals.destroy', $appraisal) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Are you sure you want to delete this appraisal?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">ID</th>
                                <td>{{ $appraisal->id }}</td>
                            </tr>
                            <tr>
                                <th>User</th>
                                <td>{{ $appraisal->user->name ?? 'N/A' }} ({{ $appraisal->user->email ?? 'N/A' }})</td>
                            </tr>
                            <tr>
                                <th>Type</th>
                                <td><span
                                        class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $appraisal->type)) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{{ $appraisal->date ? $appraisal->date->format('Y-m-d') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Achievement Score</th>
                                <td>{{ $appraisal->achievement_score ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Total Score</th>
                                <td>{{ $appraisal->total_score ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Rating</th>
                                <td>
                                    @if ($appraisal->rating)
                                        @php
                                            $badgeClass = match (strtolower($appraisal->rating)) {
                                                'outstanding' => 'bg-success',
                                                'excellent' => 'bg-success',
                                                'good' => 'bg-primary',
                                                'average' => 'bg-warning',
                                                'below average' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $appraisal->rating }}</span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Comments</th>
                                <td>{{ $appraisal->comments ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Financial Year</th>
                                <td>{{ $appraisal->financial_year }}</td>
                            </tr>
                            <tr>
                                <th>Signed by Manager</th>
                                <td>
                                    @if ($appraisal->signed_by_manager)
                                        <i class="fas fa-check-circle text-success"></i> Yes
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i> No
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $appraisal->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ $appraisal->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
