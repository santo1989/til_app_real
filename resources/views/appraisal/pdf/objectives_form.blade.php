<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Performance Objectives - {{ $financialYear }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt;
            font-weight: bold;
            color: rgba(200, 0, 0, 0.08);
            z-index: -1;
            white-space: nowrap;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header h1 {
            margin: 0 0 5px 0;
            font-size: 16pt;
            text-transform: uppercase;
        }

        .header h2 {
            margin: 0;
            font-size: 12pt;
            color: #333;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-row {
            margin-bottom: 5px;
        }

        .info-label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #e0e0e0;
            font-weight: bold;
            font-size: 9pt;
        }

        .objectives-table th:nth-child(1) {
            width: 5%;
        }

        .objectives-table th:nth-child(2) {
            width: 40%;
        }

        .objectives-table th:nth-child(3) {
            width: 10%;
        }

        .objectives-table th:nth-child(4) {
            width: 30%;
        }

        .objectives-table th:nth-child(5) {
            width: 15%;
        }

        .signature-section {
            margin-top: 30px;
        }

        .signature-box {
            display: inline-block;
            width: 45%;
            margin-right: 5%;
            vertical-align: top;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #ccc;
        }

        .page-break {
            page-break-after: always;
        }

        .summary-box {
            background-color: #f5f5f5;
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="watermark">STRICTLY CONFIDENTIAL WHEN COMPLETED</div>

    <div class="header">
        <h1>Performance Objectives Setting Form</h1>
        <h2>Financial Year: {{ $financialYear }}</h2>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Employee Name:</span>
            <span>{{ $employee->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Employee ID:</span>
            <span>{{ $employee->employee_id ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Designation:</span>
            <span>{{ $employee->designation ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Department:</span>
            <span>{{ $employee->department->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Line Manager:</span>
            <span>{{ $employee->lineManager->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date Generated:</span>
            <span>{{ now()->format('d-M-Y h:i A') }}</span>
        </div>
    </div>

    @if ($objectives->count() > 0)
        <div class="summary-box">
            <strong>Total Objectives:</strong> {{ $objectives->count() }} |
            <strong>Total Weightage:</strong> {{ $objectives->sum('weightage') }}%
            @if ($objectives->sum('weightage') != 100)
                <span style="color: red;">(Warning: Should be 100%)</span>
            @endif
        </div>

        <table class="objectives-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Objective Description</th>
                    <th>Weightage (%)</th>
                    <th>Target / Success Criteria</th>
                    <th>Type / SMART</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($objectives as $index => $objective)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $objective->description }}</td>
                        <td style="text-align: center;">{{ $objective->weightage }}%</td>
                        <td>{{ $objective->target }}</td>
                        <td>
                            <strong>Type:</strong> {{ ucfirst($objective->type) }}<br>
                            @if ($objective->is_smart)
                                <span style="color: green;">✓ SMART Verified</span>
                            @else
                                <span style="color: orange;">⚠ Not SMART</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 30px; border: 1px dashed #ccc;">
            <p style="color: #666; font-style: italic;">No objectives set for this financial year.</p>
        </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div><strong>Employee Acknowledgement</strong></div>
            <div class="signature-line">
                <div>Signature: _______________________</div>
                <div>Name: {{ $employee->name }}</div>
                <div>Date: _________________________</div>
            </div>
        </div>

        <div class="signature-box">
            <div><strong>Line Manager Approval</strong></div>
            <div class="signature-line">
                <div>Signature: _______________________</div>
                <div>Name: {{ $employee->lineManager->name ?? '_____________________' }}</div>
                <div>Date: _________________________</div>
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffc107;">
        <strong>Important Notes:</strong>
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li>Objectives can be revised within the first 9 months of the financial year</li>
            <li>Total weightage must equal 100%</li>
            <li>All objectives should follow SMART criteria (Specific, Measurable, Achievable, Relevant, Time-bound)
            </li>
            <li>This document becomes confidential once signed by both parties</li>
        </ul>
    </div>

    <div class="footer">
        <strong>STRICTLY CONFIDENTIAL WHEN COMPLETED</strong><br>
        Performance Management System | Generated: {{ now()->format('d-M-Y h:i A') }}
    </div>
</body>

</html>
