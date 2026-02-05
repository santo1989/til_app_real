<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdpMilestone extends Model
{
    protected $fillable = ['idp_id', 'title', 'description', 'resource_required', 'start_date', 'end_date', 'progress', 'status', 'attainment', 'visible_demonstration', 'hr_input', 'attained_by_id', 'attained_at'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'decimal:2',
        'attainment' => 'boolean',
        'attained_at' => 'datetime',
    ];

    public function idp()
    {
        return $this->belongsTo(Idp::class);
    }
}
