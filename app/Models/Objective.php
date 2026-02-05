<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Objective extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'department_id', 'type', 'description', 'weightage', 'target', 'is_smart', 'status', 'revised_at', 'financial_year', 'created_by', 'approved_by', 'approved_at', 'target_achieved', 'target_achieved_entered_by', 'target_achieved_entered_at', 'final_score'];

    protected $casts = [
        'revised_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_smart' => 'boolean',
        'weightage' => 'integer',
        'target_achieved_entered_at' => 'datetime',
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

    /**
     * Approver (user who approved the objective)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * The user who entered the target_achieved value.
     */
    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'target_achieved_entered_by');
    }

    /**
     * Midterm progress entries associated with this objective.
     */
    public function midtermProgresses()
    {
        return $this->hasMany(MidtermProgress::class);
    }
}
