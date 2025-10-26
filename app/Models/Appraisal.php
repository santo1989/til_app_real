<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appraisal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'date',
        'achievement_score',
        'comments',
        'action_points',
        'total_score',
        'rating',
        'signed_by_employee',
        'signed_by_manager',
        'signed_by_supervisor',
        'employee_signed_at',
        'employee_signed_by_name',
        'employee_signature_path',
        'manager_signed_at',
        'manager_signed_by_name',
        'manager_signature_path',
        'supervisor_signed_at',
        'supervisor_signed_by_name',
        'supervisor_signature_path',
        'conducted_by',
        'financial_year',
        'ratings',
        'supervisor_comments',
    ];


    protected $casts = [
        'date' => 'date',
        'achievement_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'signed_by_employee' => 'boolean',
        'signed_by_manager' => 'boolean',
        'signed_by_supervisor' => 'boolean',
        'employee_signed_at' => 'datetime',
        'manager_signed_at' => 'datetime',
        'supervisor_signed_at' => 'datetime',
        'ratings' => 'array',
    ];

    /**
     * Backwards-compatible accessor for legacy 'appraisal_type' property.
     * Some views/controllers still reference $appraisal->appraisal_type; map it to 'type'.
     */
    public function getAppraisalTypeAttribute()
    {
        return $this->attributes['type'] ?? null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conductor()
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }
}
