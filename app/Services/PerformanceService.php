<?php

namespace App\Services;

use App\Models\Objective;

class PerformanceService
{
    /**
     * Compute final weighted scores for a user in a financial year.
     * Returns per-objective collection (with computed final_score), total_score and status.
     *
     * @param int $userId
     * @param string|null $financialYearLabel
     * @return array
     */
    public function computeUserScores(int $userId, ?string $financialYearLabel): array
    {
        // Accept nullable label to be defensive in controller/tests; coerce to string
        // so queries won't fail due to a null type being passed in by callers.
        $financialYearLabel = (string) $financialYearLabel;
        $objectives = Objective::where('user_id', $userId)
            ->where('financial_year', $financialYearLabel)
            ->get();

        $total = 0.0;
        $minIndividualScore = null;
        $minAllScore = null;

        $minIndividualThreshold = config('appraisal.min_individual_threshold', 50.0);
        $outstandingIndividualThreshold = config('appraisal.outstanding_individual_threshold', 80.0);
        $outstandingTotalThreshold = config('appraisal.outstanding_total_threshold', 75.0);
        $goodTotalThreshold = config('appraisal.good_total_threshold', 60.0);
        $goodIndividualThreshold = config('appraisal.good_individual_threshold', 60.0);

        foreach ($objectives as $obj) {
            $achieved = (float) ($obj->target_achieved ?? 0.0);
            // weightage is stored as percent
            $contribution = ($achieved / 100.0) * ($obj->weightage ?? 0);
            // store a computed property for use in views/tests (do not persist)
            // contribution is already in percentage-points (e.g., 18 for 90% on 20% weight)
            $obj->computed_final_score = round($contribution, 2);
            $total += $contribution;

            // track minimums for individual and for all objectives
            if ($minAllScore === null || $achieved < $minAllScore) {
                $minAllScore = $achieved;
            }

            if ($obj->type === 'individual') {
                if ($minIndividualScore === null || $achieved < $minIndividualScore) {
                    $minIndividualScore = $achieved;
                }
            }
        }

        // Determine rating using the 5-tier taxonomy with conditional rules.
        // Taxonomy (exact labels): "Outstanding", "Excellent", "Good", "Average", "Below Average"
        // Rules:
        // - Outstanding: total >= 90 AND every individual objective >= 80
        // - Excellent: total >= 80 (fallback for outstanding when individual minima not met)
        // - Good: total >= 70 AND every objective (departmental and individual) >= 60
        // - Average: total >= 60
        // - Below Average: total < 60

        $rating = 'Below Average';

        // Evaluate descending by total, applying conditional downgrades when minima not met
        if ($total >= 90.0) {
            // Candidate for Outstanding
            if ($minIndividualScore !== null && $minIndividualScore >= $outstandingIndividualThreshold) {
                $rating = 'Outstanding';
            } else {
                // Downgrade to Excellent if per-individual minima not met
                $rating = 'Excellent';
            }
        } elseif ($total >= 80.0) {
            $rating = 'Excellent';
        } elseif ($total >= 70.0) {
            // Candidate for Good (requires every objective >= goodIndividualThreshold)
            if ($minAllScore !== null && $minAllScore >= $goodIndividualThreshold) {
                $rating = 'Good';
            } else {
                // Downgrade to Average when per-objective minima are not met
                $rating = 'Average';
            }
        } elseif ($total >= 60.0) {
            $rating = 'Average';
        } else {
            $rating = 'Below Average';
        }

        return [
            'per_objective' => $objectives,
            'total_score' => round($total, 2),
            'status' => $rating,
        ];
    }
}
