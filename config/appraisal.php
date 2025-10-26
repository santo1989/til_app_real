<?php

return [
    // Allowed objective weightages (percent)
    'allowed_weightages' => [10, 15, 20, 25],

    // Department/team total weightage required
    'departmental_total' => 30,

    // Departmental objective count (2-3 expected)
    'departmental_min_count' => 2,
    'departmental_max_count' => 3,

    // Individual objective count limits
    'individual_min' => 3,
    'individual_max' => 6,

    // Scoring thresholds (percent)
    'min_individual_threshold' => 50.0,
    'outstanding_individual_threshold' => 80.0,
    'outstanding_total_threshold' => 75.0,
    'good_total_threshold' => 60.0,
    // Minimum per-objective threshold to consider an objective 'good' (applies to both departmental and individual objectives)
    'good_individual_threshold' => 60.0,
    // If true, enforce SMART validation on all objective targets. Individual requests may also set `is_smart`.
    'enforce_is_smart' => false,
];
