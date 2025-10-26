<?php

/**
 * Rating configuration and mappings.
 *
 * This file centralizes the mapping between the human-friendly rating labels
 * shown in the UI and the DB tokens stored in the `appraisals.rating` column.
 *
 * NOTE: The application currently persists DB tokens using the older enum set
 * ['outstanding','good','average','below']. To avoid a schema migration this
 * config maps the new display labels to the existing DB-safe tokens. If you
 * later migrate the DB enum to accept the full labels verbatim update this
 * config accordingly.
 */

return [
    // Display label => DB token
    'map' => [
        'Outstanding'   => 'outstanding',
        'Excellent'     => 'good',     // maps to existing 'good' token
        'Good'          => 'good',
        'Average'       => 'average',
        'Below Average' => 'below',
        // also accept lowercase/legacy keys
        'outstanding'   => 'outstanding',
        'excellent'     => 'good',
        'good'          => 'good',
        'average'       => 'average',
        'below average' => 'below',
        'below'         => 'below',
    ],

    // Inverse mapping: DB token => preferred display label
    'display' => [
        'outstanding' => 'Outstanding',
        'good'        => 'Good',
        'average'     => 'Average',
        'below'       => 'Below Average',
    ],

    // Default fallback if an unknown label is supplied
    'default_db_token' => 'average',
];
