<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FinancialYear extends Model
{
    // support either legacy `name` or newer `label` column
    // include revision_cutoff in fillable so it can be mass assigned in tests and seeders
    protected $fillable = ['label', 'name', 'start_date', 'end_date', 'is_active', 'revision_cutoff'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'revision_cutoff',
    ];

    /**
     * Return the currently active FinancialYear model.
     */
    public static function active()
    {
        return static::where('is_active', true)->first();
    }

    public function containsDate($date)
    {
        $date = Carbon::parse($date);
        $start = Carbon::parse($this->start_date)->startOfDay();
        $end = Carbon::parse($this->end_date)->endOfDay();
        return $date->between($start, $end);
    }

    /**
     * Provide a consistent `label` attribute even when the DB has `name` (legacy).
     */
    public function getLabelAttribute($value)
    {
        if (!is_null($value) && $value !== '') {
            return $value;
        }

        return $this->attributes['name'] ?? null;
    }

    /**
     * When setting label, prefer to set the actual `label` attribute if present in the model
     * otherwise fall back to `name` so saving doesn't try to write a missing column.
     */
    public function setLabelAttribute($value)
    {
        if (array_key_exists('label', $this->attributes) || in_array('label', $this->getFillable())) {
            $this->attributes['label'] = $value;
        } else {
            $this->attributes['name'] = $value;
        }
    }

    /**
     * Alias returning the active FinancialYear model (exists for compatibility).
     */
    public static function getActive()
    {
        return static::active();
    }

    /**
     * Return the label/name string for the active financial year.
     */
    public static function getActiveName()
    {
        $a = static::active();
        if (!$a) {
            return null;
        }

        return $a->label ?? $a->name ?? null;
    }

    /**
     * Relationship: objectives associated with this financial year.
     * Objectives store the financial_year as a label string, so map by label.
     */
    public function objectives()
    {
        return $this->hasMany(\App\Models\Objective::class, 'financial_year', 'label');
    }

    /**
     * Safe accessor to always return a Collection for objectives (never null).
     */
    public function getObjectivesAttribute($value)
    {
        // If relation already loaded return it, otherwise load
        if ($this->relationLoaded('objectives')) {
            return $this->getRelation('objectives');
        }

        return $this->objectives()->get();
    }

    /**
     * Relationship: appraisals associated with this financial year.
     */
    public function appraisals()
    {
        return $this->hasMany(\App\Models\Appraisal::class, 'financial_year', 'label');
    }

    /**
     * Safe accessor to always return a Collection for appraisals (never null).
     */
    public function getAppraisalsAttribute($value)
    {
        if ($this->relationLoaded('appraisals')) {
            return $this->getRelation('appraisals');
        }

        return $this->appraisals()->get();
    }

    /**
     * Returns whether revisions are still allowed for this fiscal year.
     * Uses revision_cutoff if present, otherwise falls back to allow-by-date logic.
     */
    public function isRevisionAllowed(): bool
    {
        if (!empty($this->revision_cutoff)) {
            return now()->lessThanOrEqualTo(Carbon::parse($this->revision_cutoff));
        }

        // If no cutoff is present, allow revisions up to 9 months from start_date
        if (!empty($this->start_date)) {
            $start = Carbon::parse($this->start_date);
            $cutoff = $start->copy()->addMonths(9)->endOfDay();
            return now()->lessThanOrEqualTo($cutoff);
        }

        return true;
    }
}
