<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'table_name', 'record_id', 'details'];

    /**
     * Filter attributes to only include columns that exist in the table
     */
    public static function create(array $attributes = [])
    {
        $columns = Schema::getColumnListing('audit_logs');
        $filtered = array_filter($attributes, function ($key) use ($columns) {
            return in_array($key, $columns);
        }, ARRAY_FILTER_USE_KEY);

        return static::query()->create($filtered);
    }

    public static function record(
        string $action,
        string $details,
        ?string $tableName = null,
        ?int $recordId = null,
        ?int $userId = null
    ): self {
        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'details' => $details,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
