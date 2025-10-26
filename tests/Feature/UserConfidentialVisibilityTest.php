<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserConfidentialVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_confidential_visibility_is_restricted_to_hr_super_and_self()
    {
        $employee = User::create([
            'name' => 'Employee',
            'employee_id' => 'EMP' . uniqid(),
            'email' => 'employee@example.com',
            'password' => bcrypt('secret'),
            'role' => 'employee',
            'is_active' => true,
            'password_plain' => 'plain-secret',
        ]);

        $hr = User::create([
            'name' => 'HR',
            'employee_id' => 'EMP' . uniqid(),
            'email' => 'hr@example.com',
            'password' => bcrypt('secret'),
            'role' => 'hr_admin',
            'is_active' => true,
        ]);

        $super = User::create([
            'name' => 'Super',
            'employee_id' => 'EMP' . uniqid(),
            'email' => 'super@example.com',
            'password' => bcrypt('secret'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $lineManager = User::create([
            'name' => 'Manager',
            'employee_id' => 'EMP' . uniqid(),
            'email' => 'manager@example.com',
            'password' => bcrypt('secret'),
            'role' => 'line_manager',
            'is_active' => true,
        ]);

        // hr can view confidential
        $this->assertTrue($hr->can('viewConfidential', $employee));

        // super can view confidential
        $this->assertTrue($super->can('viewConfidential', $employee));

        // employee self can view confidential
        $this->assertTrue($employee->can('viewConfidential', $employee));

        // line manager cannot (unless it's the same user)
        $this->assertFalse($lineManager->can('viewConfidential', $employee));
    }
}
