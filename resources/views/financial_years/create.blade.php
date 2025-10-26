@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="mb-4">
            <h3>Create New Financial Year</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('financial-years.index') }}">Financial Years</a></li>
                    <li class="breadcrumb-item active">Create</li>
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
                <form action="{{ route('financial-years.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <label for="label" class="col-sm-3 col-form-label">Financial Year Label <span
                                class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control @error('label') is-invalid @enderror" id="label"
                                name="label" value="{{ old('label') }}" placeholder="e.g., 2025-26" required>
                            <small class="form-text text-muted">Format: YYYY-YY (e.g., 2025-26, 2026-27)</small>
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
                                id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                            <small class="form-text text-muted">First day of the financial year (e.g., July 1, 2025)</small>
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
                                id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                            <small class="form-text text-muted">Last day of the financial year (e.g., June 30, 2026)</small>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> Note:</strong>
                        <ul class="mb-0 mt-2">
                            <li>The revision cutoff will be automatically calculated as 9 months from the start date.</li>
                            <li>After the revision cutoff, employees and managers cannot modify objectives.</li>
                            <li>The new financial year will be created with "Upcoming" status.</li>
                            <li>You can activate it later from the financial years list.</li>
                        </ul>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('financial-years.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Financial Year
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-calculate end date when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            if (startDate) {
                // Add 1 year minus 1 day to get end date
                const endDate = new Date(startDate);
                endDate.setFullYear(endDate.getFullYear() + 1);
                endDate.setDate(endDate.getDate() - 1);

                // Format as YYYY-MM-DD
                const year = endDate.getFullYear();
                const month = String(endDate.getMonth() + 1).padStart(2, '0');
                const day = String(endDate.getDate()).padStart(2, '0');

                document.getElementById('end_date').value = `${year}-${month}-${day}`;
            }
        });

        // Auto-suggest label based on start date
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            if (startDate && !document.getElementById('label').value) {
                const year1 = startDate.getFullYear();
                const year2 = String(year1 + 1).substr(-2);
                document.getElementById('label').value = `${year1}-${year2}`;
            }
        });
    </script>
@endsection
