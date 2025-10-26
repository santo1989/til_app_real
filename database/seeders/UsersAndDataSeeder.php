<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Objective;
use App\Models\Idp;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UsersAndDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Get existing user count to ensure unique employee IDs
        $existingUserCount = User::count();
        $startId = $existingUserCount + 1;

        // Get or create departments
        $departments = Department::all();
        if ($departments->count() == 0) {
            $departments = collect([
                Department::create(['name' => 'Human Resources', 'code' => 'HR']),
                Department::create(['name' => 'Information Technology', 'code' => 'IT']),
                Department::create(['name' => 'Finance', 'code' => 'FIN']),
                Department::create(['name' => 'Marketing', 'code' => 'MKT']),
                Department::create(['name' => 'Operations', 'code' => 'OPS']),
                Department::create(['name' => 'Sales', 'code' => 'SAL']),
            ]);
        }

        // Create 2 Board Members
        echo "Creating 2 Board Members...\n";
        $boardMembers = [];
        for ($i = 1; $i <= 2; $i++) {
            $boardMembers[] = User::create([
                'name' => $faker->name,
                'employee_id' => 'BOARD-' . str_pad($startId++, 4, '0', STR_PAD_LEFT),
                'designation' => 'Board Member',
                'department_id' => $departments->random()->id,
                'date_of_joining' => $faker->dateTimeBetween('-10 years', '-5 years')->format('Y-m-d'),
                'tenure_in_current_role' => $faker->numberBetween(3, 10) . ' years',
                'email' => 'board.' . time() . '.' . $i . '@ntg.com.bd',
                'password' => Hash::make('12345678'),

                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'board',
                'line_manager_id' => null,
                'is_active' => true,
            ]);
        }
        echo "Board Members created.\n";

        // Create 3 HR Admins
        echo "Creating 3 HR Admins...\n";
        $hrAdmins = [];
        for ($i = 1; $i <= 3; $i++) {
            $hrAdmins[] = User::create([
                'name' => $faker->name,
                'employee_id' => 'HRADM-' . str_pad($startId++, 4, '0', STR_PAD_LEFT),
                'designation' => 'HR Administrator',
                'department_id' => $departments->where('code', 'HR')->first()->id ?? $departments->first()->id,
                'date_of_joining' => $faker->dateTimeBetween('-8 years', '-3 years')->format('Y-m-d'),
                'tenure_in_current_role' => $faker->numberBetween(2, 8) . ' years',
                'email' => 'hr.' . time() . '.' . $i . '@ntg.com.bd',
                'password' => Hash::make('12345678'),

                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'hr_admin',
                'line_manager_id' => $boardMembers[array_rand($boardMembers)]->id,
                'is_active' => true,
            ]);
        }
        echo "HR Admins created.\n";

        // Create 5 Line Managers
        echo "Creating 5 Line Managers...\n";
        $lineManagers = [];
        for ($i = 1; $i <= 5; $i++) {
            $lineManagers[] = User::create([
                'name' => $faker->name,
                'employee_id' => 'LMGR-' . str_pad($startId++, 4, '0', STR_PAD_LEFT),
                'designation' => $faker->randomElement(['Team Lead', 'Manager', 'Senior Manager', 'Department Manager']),
                'department_id' => $departments->random()->id,
                'date_of_joining' => $faker->dateTimeBetween('-7 years', '-2 years')->format('Y-m-d'),
                'tenure_in_current_role' => $faker->numberBetween(2, 7) . ' years',
                'email' => 'linemanager.' . time() . '.' . $i . '@ntg.com.bd',
                'password' => Hash::make('12345678'),

                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'line_manager',
                'line_manager_id' => $boardMembers[array_rand($boardMembers)]->id,
                'is_active' => true,
            ]);
        }
        echo "Line Managers created.\n";

        // Create 30 Employees
        echo "Creating 30 Employees...\n";
        $employees = [];
        for ($i = 1; $i <= 30; $i++) {
            $employees[] = User::create([
                'name' => $faker->name,
                'employee_id' => 'EMP-' . str_pad($startId++, 4, '0', STR_PAD_LEFT),
                'designation' => $faker->randomElement([
                    'Junior Developer',
                    'Senior Developer',
                    'Analyst',
                    'Senior Analyst',
                    'Executive',
                    'Senior Executive',
                    'Associate',
                    'Specialist',
                    'Coordinator',
                    'Officer',
                    'Assistant',
                    'Consultant'
                ]),
                'department_id' => $departments->random()->id,
                'date_of_joining' => $faker->dateTimeBetween('-5 years', '-1 month')->format('Y-m-d'),
                'tenure_in_current_role' => $faker->numberBetween(1, 5) . ' years',
                'email' => 'employee.' . time() . '.' . $i . '@ntg.com.bd',
                'password' => Hash::make('12345678'),

                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'employee',
                'line_manager_id' => $lineManagers[array_rand($lineManagers)]->id,
                'is_active' => $faker->boolean(95), // 95% active
            ]);
        }
        echo "Employees created.\n";

        // Combine all users for objectives and IDPs
        $allUsers = array_merge($boardMembers, $hrAdmins, $lineManagers, $employees);

        // Create 6 Objectives for each user
        echo "Creating 6 Objectives for each user...\n";
        $objectiveTypes = ['individual', 'departmental']; // Valid enum values
        $objectiveStatuses = ['draft', 'set', 'revised', 'dropped']; // Valid enum values

        foreach ($allUsers as $user) {
            for ($i = 1; $i <= 6; $i++) {
                Objective::create([
                    'user_id' => $user->id,
                    'department_id' => $user->department_id,
                    'type' => $faker->randomElement($objectiveTypes),
                    'description' => $faker->sentence(12),
                    'weightage' => $faker->randomElement([10, 15, 20, 25, 30]),
                    'target' => $faker->numberBetween(80, 100),
                    'financial_year' => $faker->randomElement(['2024-25', '2025-26']),
                    'status' => $faker->randomElement($objectiveStatuses),
                    'is_smart' => $faker->boolean(70), // 70% are SMART objectives
                    'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                    'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
                ]);
            }
        }
        echo "Objectives created for all users.\n";

        // Create 5 IDPs for each user
        echo "Creating 5 IDPs for each user...\n";

        foreach ($allUsers as $user) {
            for ($i = 1; $i <= 5; $i++) {
                $reviewDate = $faker->dateTimeBetween('-6 months', '+6 months');

                Idp::create([
                    'user_id' => $user->id,
                    'description' => $faker->sentence(15),
                    'review_date' => $reviewDate->format('Y-m-d'),
                    'progress_till_dec' => $faker->boolean(60) ? $faker->sentence(10) : null,
                    'revised_description' => $faker->boolean(40) ? $faker->sentence(12) : null,
                    'accomplishment' => $faker->boolean(50) ? $faker->sentence(8) : null,
                    'signed_by_employee' => $faker->boolean(60),
                    'signed_by_manager' => $faker->boolean(40),
                    'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                    'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
                ]);
            }
        }
        echo "IDPs created for all users.\n";

        // Summary
        echo "\n=== SEEDING COMPLETED ===\n";
        echo "Total Users Created: " . count($allUsers) . "\n";
        echo "  - Board Members: 2\n";
        echo "  - HR Admins: 3\n";
        echo "  - Line Managers: 5\n";
        echo "  - Employees: 30\n";
        echo "Total Objectives Created: " . (count($allUsers) * 6) . " (6 per user)\n";
        echo "Total IDPs Created: " . (count($allUsers) * 5) . " (5 per user)\n";
        echo "\nDefault password for all users: 12345678\n";
        echo "\nSample Employee IDs and Emails:\n";
        echo "  Board Member: " . $boardMembers[0]->employee_id . " / " . $boardMembers[0]->email . "\n";
        echo "  HR Admin: " . $hrAdmins[0]->employee_id . " / " . $hrAdmins[0]->email . "\n";
        echo "  Line Manager: " . $lineManagers[0]->employee_id . " / " . $lineManagers[0]->email . "\n";
        echo "  Employee: " . $employees[0]->employee_id . " / " . $employees[0]->email . "\n";
    }
}
