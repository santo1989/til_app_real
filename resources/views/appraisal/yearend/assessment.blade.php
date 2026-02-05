@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 text-center">Assessment on Objectives/Targets Achievements</h3>
            @if (isset($appraisal) && $appraisal)
                <x-ui.button variant="danger" href="{{ route('appraisals.yearend.pdf', ['appraisal_id' => $appraisal->id]) }}"
                    target="_blank" class="btn-sm">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </x-ui.button>
                <div class="ml-2 d-inline-block">
                    <label class="mr-2">Employee Sign:</label>
                    @php $role = 'employee'; @endphp
                    @include('appraisal.partials.signature_form', [
                        'appraisal' => $appraisal,
                        'role' => $role,
                    ])
                </div>
                <div class="ml-2 d-inline-block">
                    <label class="mr-2">Manager Sign:</label>
                    @php $role = 'manager'; @endphp
                    @include('appraisal.partials.signature_form', [
                        'appraisal' => $appraisal,
                        'role' => $role,
                    ])
                </div>
                <div class="ml-2 d-inline-block">
                    <label class="mr-2">Reviewing Officer:</label>
                    @php $role = 'supervisor'; @endphp
                    @include('appraisal.partials.signature_form', [
                        'appraisal' => $appraisal,
                        'role' => $role,
                    ])
                </div>
            @endif
        </div>

        <h5 class="mt-4">Departmental/Team Objectives (Total 30% Weightage)</h5>
        <p>During the Year End Appraisal, the achievement on the Departmental/Team Objectives will be assessed as per the
            following format and shared by the Board with the respective Department Heads/Team Lead. The line managers will
            cascade down the same through the line managers. These achievements will be same for each of the employees in
            the Department/ Team.</p>
        <form method="POST" action="{{ route('appraisal.yearend.assessment.save', $employee->id) }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>A<br>SL. #</th>
                            <th>B<br>Objectives/ Key Performance Indicator/ Action Plans</th>
                            <th>C<br>Timeline</th>
                            <th>D<br>Weightage %</th>
                            <th>E<br>% Target Achieved (TA)</th>
                            <th>F<br>Final Score<br>(W * TA * 100)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teamObjectives as $i => $obj)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $obj->description }}</td>
                                <td>{{ $obj->timeline ?? '-' }}</td>
                                <td>{{ $obj->weightage }}</td>
                                <td>
                                    <input type="hidden" name="teamObjectives[{{ $i }}][id]"
                                        value="{{ $obj->id }}">
                                    <input type="number" name="teamObjectives[{{ $i }}][target_achieved]"
                                        step="0.01" min="0" max="1" class="form-control"
                                        value="{{ $obj->target_achieved ?? '' }}">
                                </td>
                                <td>
                                    <input type="number" name="teamObjectives[{{ $i }}][final_score]"
                                        step="0.01" min="0" max="100" class="form-control"
                                        value="{{ $obj->final_score ?? '' }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No team objectives found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <h5 class="mt-5">Individual Objectives (Total 70% Weightage)</h5>
            <p>During the Year End Appraisal, the Line Manager and the Employee will have a discussion on the employee’s
                achievement during the year on the Individual Objectives set at the beginning of the year and or revised
                during
                the Year. (If any). Based on the discussion the Line Manager will record his assessment as per the following
                format:</p>
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>A<br>SL. #</th>
                            <th>B<br>Objectives/ Key Performance Indicator/ Action Plans</th>
                            <th>C<br>Timeline</th>
                            <th>D<br>Weightage % (W)</th>
                            <th>E<br>% Target Achieved (TA)</th>
                            <th>F<br>Final Score<br>(W * TA * 100)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($individualObjectives as $i => $obj)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $obj->description }}</td>
                                <td>{{ $obj->timeline ?? '-' }}</td>
                                <td>{{ $obj->weightage }}</td>
                                <td>
                                    <input type="hidden" name="individualObjectives[{{ $i }}][id]"
                                        value="{{ $obj->id }}">
                                    <input type="number" name="individualObjectives[{{ $i }}][target_achieved]"
                                        step="0.01" min="0" max="1" class="form-control"
                                        value="{{ $obj->target_achieved ?? '' }}">
                                </td>
                                <td>
                                    <input type="number" name="individualObjectives[{{ $i }}][final_score]"
                                        step="0.01" min="0" max="100" class="form-control"
                                        value="{{ $obj->final_score ?? '' }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No individual objectives found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <x-ui.button variant="success" type="submit">Save Assessment</x-ui.button>
            </div>
        </form>
    </div>
@endsection
