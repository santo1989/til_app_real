<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MidtermProgress extends Model
{
    protected $table = 'midterm_progress';

    protected $fillable = [
        'user_id',
        'objective_id',
        'financial_year',
        'progress_percent',
        'notes',
        'recorded_by',
        'recorded_at'
    ];

    protected $casts = [
        'progress_percent' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function objective(): BelongsTo
    {
        return $this->belongsTo(Objective::class);
    }
}
