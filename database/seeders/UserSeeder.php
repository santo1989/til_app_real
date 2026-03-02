<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;

class UserSeeder extends Seeder
{
    public function run()
    {
        $hr = Department::where('name', 'HR')->first();
        $mfg = Department::where('name', 'Manufacturing')->first();

        $hrAdmin = User::updateOrCreate(
            ['employee_id' => 'HR001'],
            [
                'name' => 'HR Admin',
                'email' => 'hr.admin@ntg.com.bd',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'hr_admin',
                'department_id' => $hr->id
            ]
        );

        $board = User::updateOrCreate(
            ['employee_id' => 'B001'],
            [
                'name' => 'Board Member',
                'email' => 'board@ntg.com.bd',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'board',
                'department_id' => $hr->id
            ]
        );

        $manager = User::updateOrCreate(
            ['employee_id' => 'LM001'],
            [
                'name' => 'Line Manager',
                'email' => 'manager@ntg.com.bd',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'line_manager',
                'department_id' => $mfg->id
            ]
        );

        $emp = User::updateOrCreate(
            ['employee_id' => 'E001'],
            [
                'name' => 'Employee One',
                'email' => 'employee@ntg.com.bd',
                'password' => bcrypt('12345678'),
                'password_plain' => '12345678',
                'user_image' => asset('images/users/avatar.png'),
                'role' => 'employee',
                'department_id' => $mfg->id,
                'line_manager_id' => $manager->id
            ]
        );
    }
}
