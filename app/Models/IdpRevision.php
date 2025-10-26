<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdpRevision extends Model
{
    protected $table = 'idp_revisions';
    protected $fillable = [
        'idp_id',
        'changes',
        'changed_by',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function idp()
    {
        return $this->belongsTo(Idp::class);
    }
}
