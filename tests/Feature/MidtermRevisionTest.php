<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\FinancialYear;
use App\Models\Objective;

class MidtermRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_apply_midterm_revisions_transactionally()
    {
        // Create manager and employee
        $manager = User::create([
            'name' => 'Manager',
            'employee_id' => 'MGR1',
            'email' => 'mgr1@example.com',
            'password' => bcrypt('secret'),
            'role' => 'line_manager',
        ]);

        $employee = User::create([
            'name' => 'Employee',
            'employee_id' => 'EMP1',
            'email' => 'emp1@example.com',
            'password' => bcrypt('secret'),
            'role' => 'employee',
            'line_manager_id' => $manager->id,
        ]);

        // Active FY
        FinancialYear::create([
            'label' => '2025-26',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        // Create initial objectives for employee
        $o1 = Objective::create(['user_id' => $employee->id, 'type' => 'individual', 'description' => 'A', 'weightage' => 40, 'target' => 'T1', 'financial_year' => '2025-26']);
        $o2 = Objective::create(['user_id' => $employee->id, 'type' => 'individual', 'description' => 'B', 'weightage' => 30, 'target' => 'T2', 'financial_year' => '2025-26']);
        $o3 = Objective::create(['user_id' => $employee->id, 'type' => 'individual', 'description' => 'C', 'weightage' => 30, 'target' => 'T3', 'financial_year' => '2025-26']);

        // Manager logs in and applies revisions: update o1 to 30, o2 to 30, add new 40 => total 100
        $payload = [
            'revisions' => [
                ['action' => 'update', 'id' => $o1->id, 'weightage' => 30, 'title' => 'A - updated', 'description' => 'A updated'],
                ['action' => 'update', 'id' => $o2->id, 'weightage' => 30, 'title' => 'B - updated', 'description' => 'B updated'],
                ['action' => 'add', 'title' => 'D - new', 'description' => 'New objective', 'weightage' => 40, 'type' => 'individual'],
            ],
        ];

        $res = $this->actingAs($manager)->post('/appraisal/conduct-midterm-revisions/' . $employee->id, $payload);
        $res->assertRedirect();
        $res->assertSessionHasNoErrors();

        // Verify objectives now sum to 100 and include the new objective
        $sum = Objective::where('user_id', $employee->id)->where('financial_year', '2025-26')->sum('weightage');
        $this->assertEquals(100, $sum);
        $this->assertDatabaseHas('objectives', ['user_id' => $employee->id, 'description' => 'D - new']);
    }
}
