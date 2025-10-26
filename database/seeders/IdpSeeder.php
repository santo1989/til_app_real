<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Idp;
use App\Models\User;

class IdpSeeder extends Seeder
{
    public function run()
    {
        $emp = User::where('employee_id', 'E001')->first();
        Idp::create(['user_id' => $emp->id, 'description' => 'Training on new CNC machines', 'review_date' => now()->addMonths(6)]);
    }
}
