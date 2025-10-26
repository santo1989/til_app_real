<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FinancialYear;
use App\Services\FinancialYearService;
use Carbon\Carbon;

class FinancialYearServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dates_and_cutoffs()
    {
        // Create an FY starting 2025-07-01 to 2026-06-30
        $fy = FinancialYear::create([
            'label' => '2025-26',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $svc = new FinancialYearService($fy);

        $this->assertEquals('2025-07-01', $svc->getStart()->toDateString());
        $this->assertEquals('2026-06-30', $svc->getEnd()->toDateString());
        $this->assertEquals('2026-01-01', $svc->midtermDate()->toDateString());
        // The service computes ninth-month cutoff as start + 9 months -> 2026-04-01
        $this->assertEquals('2026-04-01', $svc->ninthMonthCutoff()->toDateString());

        // First month detection
        Carbon::setTestNow('2025-07-15');
        $this->assertTrue($svc->isWithinFirstMonth(now()));

        // After ninth month
        Carbon::setTestNow('2026-05-01');
        $this->assertFalse($svc->isBeforeNinthMonth(now()));

        Carbon::setTestNow();
    }
}
