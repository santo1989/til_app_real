<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialYear;
use App\Services\FinancialYearService;

class FyCheck extends Command
{
    protected $signature = 'app:fy-check';
    protected $description = 'Create sample FinancialYear (if missing) and print computed dates via FinancialYearService';

    public function handle()
    {
        $label = '2025-26';
        $fy = FinancialYear::firstOrCreate(
            ['label' => $label],
            ['start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'is_active' => true]
        );

        // ensure only this is active
        FinancialYear::where('id', '!=', $fy->id)->update(['is_active' => false]);
        $fy->update(['is_active' => true]);

        $svc = new FinancialYearService($fy);

        $this->info('Financial Year: ' . $fy->label);
        $this->line('Start: ' . $svc->getStart()->toDateString());
        $this->line('Midterm (6 months): ' . $svc->midtermDate()->toDateString());
        $this->line('9th month cutoff: ' . $svc->ninthMonthCutoff()->toDateString());
        $this->line('Year end: ' . $svc->yearEndDate()->toDateString());

        return 0;
    }
}
