<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Year-End Appraisal - {{ $financialYear }}</title>
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

        .rating-excellent {
            background-color: #d4edda;
        }

        .rating-good {
            background-color: #d1ecf1;
        }

        .rating-average {
            background-color: #fff3cd;
        }

        .rating-below {
            background-color: #f8d7da;
        }

        .signature-section {
            margin-top: 30px;
        }

        .signature-box {
            display: inline-block;
            width: 30%;
            margin-right: 3%;
            vertical-align: top;
            margin-bottom: 20px;
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

        .section-header {
            background-color: #28a745;
            color: white;
            padding: 8px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .comments-box {
            border: 1px solid #ccc;
            padding: 10px;
            min-height: 60px;
            background-color: #f9f9f9;
        }

        .summary-card {
            background-color: #e7f3ff;
            border: 2px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }

        .summary-card h3 {
            margin: 0 0 10px 0;
            color: #007bff;
        }

        .overall-rating {
            font-size: 24pt;
            font-weight: bold;
            color: #28a745;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="watermark">STRICTLY CONFIDENTIAL WHEN COMPLETED</div>

    <div class="header">
        <h1>Year-End Performance Appraisal</h1>
        <h2>Financial Year: {{ $financialYear }}</h2>
        <h2 style="color: #28a745;">Complete Annual Assessment (July - June)</h2>
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
            <span class="info-label">Appraisal Date:</span>
            <span>{{ optional($appraisal->created_at)->format('d-M-Y') ?? now()->format('d-M-Y') }}</span>
        </div>
    </div>

    <div class="section-header">PART A: ANNUAL OBJECTIVE ASSESSMENT</div>

    @if ($objectives->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 30%;">Objective</th>
                    <th style="width: 10%;">Weightage</th>
                    <th style="width: 30%;">Annual Achievement / Evidence</th>
                    <th style="width: 15%;">Final Rating</th>
                    <th style="width: 10%;">Score</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalScore = 0;
                    $ratings = json_decode($appraisal->ratings ?? '{}', true);
                    $comments = json_decode($appraisal->comments ?? '{}', true);
                @endphp
                @foreach ($objectives as $index => $objective)
                    @php
                        $objId = $objective->id;
                        $rating = $ratings[$objId] ?? 'N/A';
                        $comment = $comments[$objId] ?? 'No feedback provided';

                        // Rating to score conversion
                        $ratingScore = 0;
                        switch ($rating) {
                            case 'excellent':
                                $ratingScore = 5;
                                $ratingClass = 'rating-excellent';
                                break;
                            case 'good':
                                $ratingScore = 4;
                                $ratingClass = 'rating-good';
                                break;
                            case 'average':
                                $ratingScore = 3;
                                $ratingClass = 'rating-average';
                                break;
                            case 'below':
                                $ratingScore = 2;
                                $ratingClass = 'rating-below';
                                break;
                            default:
                                $ratingScore = 0;
                                $ratingClass = '';
                                break;
                        }

                        $objectiveScore = ($ratingScore * $objective->weightage) / 5;
                        $totalScore += $objectiveScore;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $objective->description }}</strong><br>
                            <small><em>Target: {{ $objective->target }}</em></small>
                        </td>
                        <td style="text-align: center;">{{ $objective->weightage }}%</td>
                        <td style="font-size: 9pt;">{{ $comment }}</td>
                        <td class="{{ $ratingClass }}" style="text-align: center;">
                            <strong>{{ strtoupper($rating) }}</strong><br>
                            <small>({{ $ratingScore }}/5)</small>
                        </td>
                        <td style="text-align: center;">
                            <strong>{{ number_format($objectiveScore, 1) }}</strong>
                        </td>
                    </tr>
                @endforeach
                <tr style="background-color: #f0f0f0;">
                    <td colspan="5" style="text-align: right;"><strong>Total Annual Score:</strong></td>
                    <td style="text-align: center;">
                        <strong style="font-size: 12pt;">{{ number_format($totalScore, 1) }}/100</strong>
                    </td>
                </tr>
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 30px; border: 1px dashed #ccc;">
            <p style="color: #666; font-style: italic;">No objectives found for year-end assessment.</p>
        </div>
    @endif

    @php
        // Determine overall rating based on score
        $overallRating = 'N/A';
        $overallColor = '#666';
        if ($totalScore >= 85) {
            $overallRating = 'EXCELLENT';
            $overallColor = '#28a745';
        } elseif ($totalScore >= 70) {
            $overallRating = 'GOOD';
            $overallColor = '#17a2b8';
        } elseif ($totalScore >= 60) {
            $overallRating = 'AVERAGE';
            $overallColor = '#ffc107';
        } elseif ($totalScore > 0) {
            $overallRating = 'BELOW EXPECTATIONS';
            $overallColor = '#dc3545';
        }
    @endphp

    <div class="summary-card">
        <h3>OVERALL PERFORMANCE RATING</h3>
        <div class="overall-rating" style="color: {{ $overallColor }};">{{ $overallRating }}</div>
        <div style="font-size: 14pt; margin-top: 5px;">Score: {{ number_format($totalScore, 1) }}/100</div>
    </div>

    <div class="section-header">PART B: EMPLOYEE SELF-ASSESSMENT</div>
    <div class="comments-box">
        {{ $appraisal->self_assessment ?? 'No self-assessment provided.' }}
    </div>

    <div class="section-header">PART C: MANAGER'S COMPREHENSIVE FEEDBACK</div>
    <div class="comments-box">
        {{ $appraisal->manager_comments ?? 'No manager comments provided.' }}
    </div>

    <div class="section-header">PART H: SUPERVISOR COMMENTS</div>
    <div class="comments-box">
        {{ $appraisal->supervisor_comments ?? 'No supervisor comments provided.' }}
    </div>

    <div class="section-header">PART D: KEY STRENGTHS</div>
    <div class="comments-box">
        {{ $appraisal->strengths ?? 'To be discussed during appraisal meeting.' }}
    </div>

    <div class="section-header">PART E: AREAS FOR IMPROVEMENT</div>
    <div class="comments-box">
        {{ $appraisal->areas_for_improvement ?? 'To be discussed during appraisal meeting.' }}
    </div>

    <div class="section-header">PART F: TRAINING & DEVELOPMENT RECOMMENDATIONS</div>
    <div class="comments-box">
        {{ $appraisal->training_recommendations ?? 'To be updated in IDP for next financial year.' }}
    </div>

    <div class="section-header">PART G: CAREER PROGRESSION DISCUSSION</div>
    <div class="comments-box">
        {{ $appraisal->career_discussion ?? 'Career development path to be discussed.' }}
    </div>

    @if ($totalScore > 0 && $totalScore < 60)
        <div style="margin-top: 20px; padding: 15px; background-color: #f8d7da; border: 2px solid #dc3545;">
            <strong style="color: #721c24;">⚠ PERFORMANCE IMPROVEMENT PLAN (PIP) REQUIRED</strong><br>
            <p style="margin: 5px 0;">Performance below expectations. A formal Performance Improvement Plan must be
                initiated within 15 days. HR will be notified automatically.</p>
        </div>
    @endif

    <div style="margin-top: 20px; padding: 10px; background-color: #d1ecf1; border: 1px solid #17a2b8;">
        <strong>Performance Rating Scale:</strong><br>
        <span class="rating-excellent" style="padding: 2px 5px;">Excellent (85-100)</span> = Consistently exceeds
        expectations |
        <span class="rating-good" style="padding: 2px 5px;">Good (70-84)</span> = Exceeds expectations |
        <span class="rating-average" style="padding: 2px 5px;">Average (60-69)</span> = Meets expectations |
        <span class="rating-below" style="padding: 2px 5px;">Below (&lt;60)</span> = Below expectations - PIP required
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div><strong>Employee Signature</strong></div>
            <div class="signature-line">
                @if (isset($appraisal) && $appraisal->employee_signature_path)
                    <div>
                        <img src="{{ public_path('storage/' . $appraisal->employee_signature_path) }}"
                            style="max-width:250px; max-height:80px;" alt="Employee Signature" />
                    </div>
                    <div>Name: {{ $appraisal->employee_signed_by_name ?? $employee->name }}</div>
                    <div>Date: {{ optional($appraisal->employee_signed_at)->format('d-M-Y') ?? '' }}</div>
                @elseif(isset($appraisal) && $appraisal->signed_by_employee)
                    <div>Signature: {{ $appraisal->employee_signed_by_name ?? $employee->name }}</div>
                    <div>Name: {{ $appraisal->employee_signed_by_name ?? $employee->name }}</div>
                    <div>Date: {{ optional($appraisal->employee_signed_at)->format('d-M-Y') ?? '' }}</div>
                @else
                    <div>Signature: _______________</div>
                    <div>Name: {{ $employee->name }}</div>
                    <div>Date: ________________</div>
                @endif
            </div>
        </div>

        <div class="signature-box">
            <div><strong>Line Manager Signature</strong></div>
            <div class="signature-line">
                @if (isset($appraisal) && $appraisal->manager_signature_path)
                    <div>
                        <img src="{{ public_path('storage/' . $appraisal->manager_signature_path) }}"
                            style="max-width:250px; max-height:80px;" alt="Manager Signature" />
                    </div>
                    <div>Name: {{ $appraisal->manager_signed_by_name ?? ($employee->lineManager->name ?? '') }}</div>
                    <div>Date: {{ optional($appraisal->manager_signed_at)->format('d-M-Y') ?? '' }}</div>
                @elseif(isset($appraisal) && $appraisal->signed_by_manager)
                    <div>Signature: {{ $appraisal->manager_signed_by_name ?? ($employee->lineManager->name ?? '') }}
                    </div>
                    <div>Name: {{ $appraisal->manager_signed_by_name ?? ($employee->lineManager->name ?? '') }}</div>
                    <div>Date: {{ optional($appraisal->manager_signed_at)->format('d-M-Y') ?? '' }}</div>
                @else
                    <div>Signature: _______________</div>
                    <div>Name: {{ $employee->lineManager->name ?? '______________' }}</div>
                    <div>Date: ________________</div>
                @endif
            </div>
        </div>

        <div class="signature-box">
            <div><strong>Reviewing Officer</strong></div>
            <div class="signature-line">
                @if (isset($appraisal) && $appraisal->supervisor_signature_path)
                    <div>
                        <img src="{{ public_path('storage/' . $appraisal->supervisor_signature_path) }}"
                            style="max-width:250px; max-height:80px;" alt="Supervisor Signature" />
                    </div>
                    <div>Name: {{ $appraisal->supervisor_signed_by_name }}</div>
                    <div>Date: {{ optional($appraisal->supervisor_signed_at)->format('d-M-Y') ?? '' }}</div>
                @elseif(isset($appraisal) && $appraisal->supervisor_signed_by_name)
                    <div>Signature: {{ $appraisal->supervisor_signed_by_name }}</div>
                    <div>Name: {{ $appraisal->supervisor_signed_by_name }}</div>
                    <div>Date: {{ optional($appraisal->supervisor_signed_at)->format('d-M-Y') ?? '' }}</div>
                @else
                    <div>Signature: _______________</div>
                    <div>Name: _________________</div>
                    <div>Date: ________________</div>
                @endif
            </div>
        </div>
    </div>

    @php
        $hrPath = $appraisal->hr_signature_path ?? ($hrSignaturePath ?? null);
    @endphp
    @if (!empty($hrPath) || (!empty($appraisal->signed_by_hr) && ($appraisal->hr_signed_by_name ?? false)))
        <div class="signature-section" style="margin-top:10px;">
            <div class="signature-box" style="width:30%;">
                <div><strong>HR / Admin Signature</strong></div>
                <div class="signature-line">
                    @if (!empty($hrPath))
                        <div>
                            <img src="{{ public_path('storage/' . $hrPath) }}"
                                style="max-width:250px; max-height:80px;" alt="HR Signature" />
                        </div>
                    @endif
                    <div>Name: {{ $appraisal->hr_signed_by_name ?? 'HR' }}</div>
                    <div>Date: {{ optional($appraisal->hr_signed_at)->format('d-M-Y') ?? '' }}</div>
                </div>
            </div>
        </div>
    @endif

    <div style="margin-top: 30px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffc107;">
        <strong>Employee Acknowledgement:</strong> I have reviewed this appraisal with my Line Manager and understand
        the ratings and feedback provided. My signature indicates acknowledgement, not necessarily agreement. I may add
        comments if needed.
    </div>

    <div class="footer">
        <strong>STRICTLY CONFIDENTIAL WHEN COMPLETED</strong><br>
        Year-End Performance Appraisal | Generated: {{ now()->format('d-M-Y h:i A') }}
    </div>
</body>

</html>
