<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pip extends Model
{
    protected $table = 'pips';

    protected $fillable = [
        'user_id',
        'appraisal_id',
        'status',
        'reason',
        'created_by',
        'start_date',
        'end_date',
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appraisal()
    {
        return $this->belongsTo(Appraisal::class);
    }
}
