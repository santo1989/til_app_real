@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Appraisal Details #{{ $appraisal->id }}</h4>
                        <div>
                            <x-ui.button variant="warning" href="{{ route('appraisals.edit', $appraisal) }}"
                                class="btn-sm">Edit</x-ui.button>
                            <x-ui.button variant="secondary" href="{{ route('appraisals.index') }}" class="btn-sm">Back to
                                List</x-ui.button>
                            <form action="{{ route('appraisals.destroy', $appraisal) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Are you sure you want to delete this appraisal?');">
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
                                                'below' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                            $displayRating = \App\Support\Rating::toDisplayLabel($appraisal->rating);
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $displayRating }}</span>
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
                                <th>Employee Signature</th>
                                <td>
                                    @if (!empty($appraisal->employee_signature_path))
                                        <img src="{{ asset('storage/' . $appraisal->employee_signature_path) }}"
                                            alt="Employee Signature" style="max-width:300px; max-height:100px;" />
                                        <div class="small text-muted">{{ $appraisal->employee_signed_by_name ?? '' }} -
                                            {{ optional($appraisal->employee_signed_at)->format('d M, Y') ?? '' }}</div>
                                    @elseif($appraisal->signed_by_employee)
                                        <div>Signed (no image): {{ $appraisal->employee_signed_by_name ?? 'Employee' }}
                                        </div>
                                    @else
                                        <div class="text-muted">Not signed</div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Manager Signature</th>
                                <td>
                                    @if (!empty($appraisal->manager_signature_path))
                                        <img src="{{ asset('storage/' . $appraisal->manager_signature_path) }}"
                                            alt="Manager Signature" style="max-width:300px; max-height:100px;" />
                                        <div class="small text-muted">{{ $appraisal->manager_signed_by_name ?? '' }} -
                                            {{ optional($appraisal->manager_signed_at)->format('d M, Y') ?? '' }}</div>
                                    @elseif($appraisal->signed_by_manager)
                                        <div>Signed (no image): {{ $appraisal->manager_signed_by_name ?? 'Manager' }}</div>
                                    @else
                                        <div class="text-muted">Not signed</div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Supervisor Signature</th>
                                <td>
                                    @if (!empty($appraisal->supervisor_signature_path))
                                        <img src="{{ asset('storage/' . $appraisal->supervisor_signature_path) }}"
                                            alt="Supervisor Signature" style="max-width:300px; max-height:100px;" />
                                        <div class="small text-muted">{{ $appraisal->supervisor_signed_by_name ?? '' }} -
                                            {{ optional($appraisal->supervisor_signed_at)->format('d M, Y') ?? '' }}</div>
                                    @elseif($appraisal->supervisor_signed_by_name)
                                        <div>Signed (no image): {{ $appraisal->supervisor_signed_by_name }}</div>
                                    @else
                                        <div class="text-muted">Not signed</div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>HR Signature</th>
                                <td>
                                    @if (!empty($appraisal->hr_signature_path))
                                        <img src="{{ asset('storage/' . $appraisal->hr_signature_path) }}"
                                            alt="HR Signature" style="max-width:300px; max-height:100px;" />
                                        <div class="small text-muted">{{ $appraisal->hr_signed_by_name ?? '' }} -
                                            {{ optional($appraisal->hr_signed_at)->format('d M, Y') ?? '' }}</div>
                                    @elseif($appraisal->signed_by_hr)
                                        <div>Signed (no image): {{ $appraisal->hr_signed_by_name ?? 'HR' }}</div>
                                    @else
                                        <div class="text-muted">Not signed</div>
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
