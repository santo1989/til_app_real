<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FinancialYear;
use Carbon\Carbon;

class FinancialYearRevisionCutoffTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function default_revision_cutoff_allows_revisions_up_to_nine_months_from_start()
    {
        $start = Carbon::parse('2025-01-01');
        Carbon::setTestNow($start->copy()->addMonths(9)->subDay());

        $fy = FinancialYear::create([
            'label' => '2025-26',
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addYear()->subDay()->toDateString(),
            'revision_cutoff' => null,
        ]);

        $this->assertTrue($fy->isRevisionAllowed());

        // Move beyond 9 months
        Carbon::setTestNow($start->copy()->addMonths(9)->addDay());
        $this->assertFalse($fy->isRevisionAllowed());
    }

    /** @test */
    public function explicit_revision_cutoff_is_respected_over_default()
    {
        $start = Carbon::parse('2025-01-01');
        $explicitCutoff = $start->copy()->addMonths(2)->endOfDay();

        $fy = FinancialYear::create([
            'label' => '2025-26-ec',
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addYear()->subDay()->toDateString(),
            'revision_cutoff' => $explicitCutoff->toDateTimeString(),
        ]);

        // before explicit cutoff
        Carbon::setTestNow($start->copy()->addMonths(2)->subDay());
        $this->assertTrue($fy->isRevisionAllowed());

        // after explicit cutoff
        Carbon::setTestNow($start->copy()->addMonths(2)->addDay());
        $this->assertFalse($fy->isRevisionAllowed());
    }
}
