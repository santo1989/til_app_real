@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Objectives for {{ $employee->name }} (FY {{ $financialYear }})</h3>

        <form method="get" class="mb-3">
            <label for="fy">Financial Year</label>
            <select name="fy" id="fy" onchange="this.form.submit()" class="form-control w-auto d-inline-block ms-2">
                @foreach ($years as $y)
                    <option value="{{ $y }}" {{ $y === $financialYear ? 'selected' : '' }}>{{ $y }}
                    </option>
                @endforeach
            </select>
        </form>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($canManage)
            <a href="{{ route('users.objectives.create', ['user_id' => $employee->id, 'fy' => $financialYear]) }}"
                class="btn btn-primary mb-3">Add Objective</a>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Target</th>
                    <th>Weightage</th>
                    <th>Status</th>
                    @if ($canManage)
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($objectives as $idx => $obj)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $obj->description }}</td>
                        <td>{{ $obj->target }}</td>
                        <td>{{ $obj->weightage }}%</td>
                        <td>{{ ucfirst($obj->status ?? 'set') }}</td>
                        @if ($canManage)
                            <td>
                                <a href="{{ route('users.objectives.edit', ['user_id' => $employee->id, 'objective' => $obj->id, 'fy' => $financialYear]) }}"
                                    class="btn btn-sm btn-secondary">Edit</a>
                                <form
                                    action="{{ route('users.objectives.destroy', ['user_id' => $employee->id, 'objective' => $obj->id]) }}"
                                    method="post" class="d-inline" onsubmit="return confirm('Delete this objective?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No objectives found for this year.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
