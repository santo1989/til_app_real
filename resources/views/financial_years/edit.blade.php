@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="mb-4">
            <h3>Edit Financial Year: {{ $financialYear->label }}</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('financial-years.index') }}">Financial Years</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('financial-years.show', $financialYear) }}">{{ $financialYear->label }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error!</strong> Please fix the following issues:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('financial-years.update', $financialYear) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <label for="label" class="col-sm-3 col-form-label">Financial Year Label <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control @error('label') is-invalid @enderror" id="label"
                                name="label" value="{{ old('label', $financialYear->label) }}" placeholder="e.g., 2025-26"
                                required>
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="start_date" class="col-sm-3 col-form-label">Start Date <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                id="start_date" name="start_date"
                                value="{{ old('start_date', optional($financialYear->start_date)->format('Y-m-d') ?? '') }}"
                                required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="end_date" class="col-sm-3 col-form-label">End Date <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                id="end_date" name="end_date"
                                value="{{ old('end_date', optional($financialYear->end_date)->format('Y-m-d') ?? '') }}"
                                required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <strong><i class="fas fa-exclamation-triangle"></i> Warning:</strong>
                        Changing dates for an active financial year may affect existing objectives and appraisals.
                    </div>

                    <div class="text-end">
                        <x-ui.button variant="secondary" href="{{ route('financial-years.show', $financialYear) }}">
                            <i class="fas fa-times"></i> Cancel
                        </x-ui.button>
                        <x-ui.button variant="primary" type="submit">
                            <i class="fas fa-save"></i> Update Financial Year
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
