<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Objective extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'department_id', 'type', 'description', 'weightage', 'target', 'is_smart', 'status', 'revised_at', 'financial_year', 'created_by', 'target_achieved', 'final_score'];

    protected $casts = [
        'revised_at' => 'datetime',
        'is_smart' => 'boolean',
        'weightage' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
