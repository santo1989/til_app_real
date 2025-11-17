<?php

namespace App\Services;

use App\Models\FinancialYear;
use Carbon\Carbon;

class FinancialYearService
{
    protected FinancialYear $fy;

    public function __construct(?FinancialYear $fy = null)
    {
        $this->fy = $fy ?? FinancialYear::active();
    }

    public function getStart(): Carbon
    {
        return Carbon::parse($this->fy->start_date)->startOfDay();
    }

    public function getEnd(): Carbon
    {
        return Carbon::parse($this->fy->end_date)->endOfDay();
    }

    public function midtermDate(): Carbon
    {
        return $this->getStart()->copy()->addMonths(6)->startOfDay();
    }

    public function ninthMonthCutoff(): Carbon
    {
        return $this->getStart()->copy()->addMonths(9)->endOfDay();
    }

    public function yearEndDate(): Carbon
    {
        return $this->getEnd()->copy();
    }

    public function isWithinFirstMonth($date): bool
    {
        $d = Carbon::parse($date);
        return $d->between($this->getStart(), $this->getStart()->copy()->addMonth()->endOfDay());
    }

    public function isBeforeNinthMonth($date): bool
    {
        $d = Carbon::parse($date);
        return $d->lte($this->ninthMonthCutoff());
    }

    public function label(): string
    {
        return $this->fy->label;
    }
}
