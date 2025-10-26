<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Idp extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'financial_year',
        'description',
        'progress_till_dec',
        'revised_description',
        'accomplishment',
        'review_date',
        'status',
        'signed_by_employee',
        'employee_signed_by_name',
        'employee_signed_at',
        'employee_signature_path',
        'signed_by_manager',
        'manager_signed_by_name',
        'manager_signed_at',
        'manager_signature_path',
    ];

    protected $casts = [
        'review_date' => 'date',
        'signed_by_employee' => 'boolean',
        'signed_by_manager' => 'boolean',
        'employee_signed_at' => 'datetime',
        'manager_signed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function milestones()
    {
        return $this->hasMany(IdpMilestone::class);
    }
}
