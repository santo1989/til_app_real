<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create or update Super Admin Account
        User::updateOrCreate(
            ['employee_id' => 'SUPER001'],
            [
                'name' => 'Super Administrator',
                'designation' => 'System Administrator',
                'department_id' => null, // Super admin doesn't belong to any department
                'date_of_joining' => now(),
                'tenure_in_current_role' => 'N/A',
                'email' => 'admin@ntg.com.bd',
                'password' => Hash::make('12345678'),
                'role' => 'super_admin',
                'line_manager_id' => null, // Super admin has no line manager
                'is_active' => true,
            ]
        );

        $this->command->info('Super Admin account created successfully!');
        $this->command->info('Email: admin@ntg.com.bd');
        $this->command->info('Password: 12345678');
    }
}
