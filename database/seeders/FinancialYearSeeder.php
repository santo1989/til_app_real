<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialYear;
use Carbon\Carbon;

class FinancialYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $years = [
            [
                'name' => '2024-25',
                'start_date' => '2024-07-01',
                'end_date' => '2025-06-30',
                'status' => 'closed',
                'is_active' => false,
            ],
            [
                'name' => '2025-26',
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
                'status' => 'active',
                'is_active' => true,
            ],
            [
                'name' => '2026-27',
                'start_date' => '2026-07-01',
                'end_date' => '2027-06-30',
                'status' => 'upcoming',
                'is_active' => false,
            ],
            [
                'name' => '2027-28',
                'start_date' => '2027-07-01',
                'end_date' => '2028-06-30',
                'status' => 'upcoming',
                'is_active' => false,
            ],
        ];

        foreach ($years as $year) {
            $startDate = Carbon::parse($year['start_date']);
            $revisionCutoff = $startDate->copy()->addMonths(9)->endOfDay();

            FinancialYear::create([
                'name' => $year['name'],
                'start_date' => $year['start_date'],
                'end_date' => $year['end_date'],
                'revision_cutoff' => $revisionCutoff,
                'status' => $year['status'],
                'is_active' => $year['is_active'],
            ]);
        }
    }
}
