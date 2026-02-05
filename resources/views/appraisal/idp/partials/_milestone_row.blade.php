@php
    // $milestone, $idp expected
    $canUpdate = auth()->user()->can('update', $idp);
    $canAttain = auth()->user()->can('attain', $idp);
@endphp
<tr data-id="{{ $milestone->id }}" data-attainment="{{ $milestone->attainment ? 1 : 0 }}"
    data-visible="{{ e($milestone->visible_demonstration) }}" data-hr="{{ e($milestone->hr_input) }}">
    <td class="m-title">{{ $milestone->title }}</td>
    <td class="m-start">{{ $milestone->start_date?->format('Y-m-d') ?? 'N/A' }}</td>
    <td class="m-end">{{ $milestone->end_date?->format('Y-m-d') ?? 'N/A' }}</td>
    <td class="m-progress">{{ $milestone->progress ?? 0 }}%</td>
    <td class="m-status">{{ ucfirst(str_replace('_', ' ', $milestone->status)) }}</td>
    <td class="m-attainment">{{ $milestone->attainment ? 'Yes' : 'No' }}</td>
    <td class="m-visible">{{ \Illuminate\Support\Str::limit($milestone->visible_demonstration, 80) }}</td>
    <td class="m-hr">{{ \Illuminate\Support\Str::limit($milestone->hr_input, 80) }}</td>
    <td class="m-actions">
        @if ($canUpdate)
            <button class="btn btn-sm btn-outline-secondary edit-milestone">Edit</button>
            <button class="btn btn-sm btn-outline-danger delete-milestone">Remove</button>
        @endif
        @if ($canAttain)
            <button class="btn btn-sm btn-outline-success ms-1 attain-milestone">Mark/Update Attainment</button>
        @endif
    </td>
</tr>
<tr class="edit-row d-none" data-id="edit-{{ $milestone->id }}">
    <td colspan="9">
        <form class="edit-milestone-form" data-id="{{ $milestone->id }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-3"><input name="title" class="form-control" value="{{ $milestone->title }}"
                        required></div>
                <div class="col-md-2"><input type="date" name="start_date" class="form-control"
                        value="{{ $milestone->start_date?->format('Y-m-d') }}"></div>
                <div class="col-md-2"><input type="date" name="end_date" class="form-control"
                        value="{{ $milestone->end_date?->format('Y-m-d') }}"></div>
                <div class="col-md-2"><input type="number" name="progress" class="form-control" min="0"
                        max="100" value="{{ $milestone->progress ?? 0 }}"></div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="open" {{ $milestone->status == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ $milestone->status == 'in_progress' ? 'selected' : '' }}>In
                            Progress</option>
                        <option value="completed" {{ $milestone->status == 'completed' ? 'selected' : '' }}>Completed
                        </option>
                        <option value="blocked" {{ $milestone->status == 'blocked' ? 'selected' : '' }}>Blocked
                        </option>
                    </select>
                </div>
                <div class="col-md-1"><button class="btn btn-sm btn-outline-primary">Save</button></div>
            </div>
        </form>
    </td>
</tr>
