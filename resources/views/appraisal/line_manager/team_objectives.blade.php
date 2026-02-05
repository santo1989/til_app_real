@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Team Objectives</h5>
            <p>Set or review objectives for your direct reports.</p>
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Objectives Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($team as $i => $member)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->department->name ?? 'N/A' }}</td>
                            <td>{{ $member->objectives->count() }}</td>
                            <td>
                                <x-ui.button variant="primary" href="{{ route('objectives.show_set_for_user', $member->id) }}"
                                    class="btn-sm">Set Objectives</x-ui.button>
                                <x-ui.button variant="primary"
                                    href="{{ route('users.objectives.index', ['user_id' => $member->id]) }}"
                                    class="btn-sm">View</x-ui.button>
                                <x-ui.button variant="secondary"
                                    href="{{ route('appraisals.conduct_midterm', $member->id) }}"
                                    class="btn-sm">Midterm</x-ui.button>
                                <x-ui.button variant="info" href="{{ route('appraisals.conduct_yearend', $member->id) }}"
                                    class="btn-sm">Year-End</x-ui.button>
                                <x-ui.button variant="success"
                                    href="{{ route('appraisal.yearend.assessment', $member->id) }}" class="btn-sm">Year-End
                                    Assessment</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
