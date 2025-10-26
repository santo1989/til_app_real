<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdpMilestone extends Model
{
    protected $fillable = ['idp_id', 'title', 'description', 'resource_required', 'start_date', 'end_date', 'progress', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'decimal:2',
    ];

    public function idp()
    {
        return $this->belongsTo(Idp::class);
    }
}
