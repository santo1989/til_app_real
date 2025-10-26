<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Objective;
use App\Models\User;

class ObjectiveSeeder extends Seeder
{
    public function run()
    {
        $emp = User::where('employee_id', 'E001')->first();
        // Individual objectives: total 70%
        Objective::create(['user_id' => $emp->id, 'type' => 'individual', 'description' => 'Increase production efficiency', 'weightage' => 25, 'target' => '5% reduction in downtime', 'is_smart' => true, 'status' => 'set', 'financial_year' => '2025-26']);
        Objective::create(['user_id' => $emp->id, 'type' => 'individual', 'description' => 'Reduce scrap rate', 'weightage' => 20, 'target' => 'Scrap <2%', 'is_smart' => true, 'status' => 'set', 'financial_year' => '2025-26']);
        Objective::create(['user_id' => $emp->id, 'type' => 'individual', 'description' => 'Improve on-time delivery', 'weightage' => 15, 'target' => 'OTD > 98%', 'is_smart' => true, 'status' => 'set', 'financial_year' => '2025-26']);
        Objective::create(['user_id' => $emp->id, 'type' => 'individual', 'description' => 'Reduce energy consumption', 'weightage' => 10, 'target' => 'Reduce kWh/unit by 3%', 'is_smart' => true, 'status' => 'set', 'financial_year' => '2025-26']);
    }
}
