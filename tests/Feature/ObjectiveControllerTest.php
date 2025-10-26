<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\FinancialYear;

class ObjectiveControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_objective_submit_success_and_failure()
    {
        // create employee
        $user = User::create([
            'name' => 'Employee',
            'employee_id' => 'EMP200',
            'email' => 'emp200@example.com',
            'password' => bcrypt('secret'),
            'role' => 'employee',
        ]);

        // create active financial year
        FinancialYear::create([
            'label' => '2025-26',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $payload = [
            'objectives' => [
                ['type' => 'individual', 'description' => 'A', 'weightage' => 40, 'target' => 'T1'],
                ['type' => 'individual', 'description' => 'B', 'weightage' => 30, 'target' => 'T2'],
                ['type' => 'individual', 'description' => 'C', 'weightage' => 30, 'target' => 'T3'],
            ],
        ];

        $response = $this->actingAs($user)->post('/appraisal/submit-objective-setting', $payload);
        $response->assertRedirect();

        // Now submit invalid payload (sum != 100)
        $bad = [
            'objectives' => [
                ['type' => 'individual', 'description' => 'A', 'weightage' => 40, 'target' => 'T1'],
                ['type' => 'individual', 'description' => 'B', 'weightage' => 30, 'target' => 'T2'],
                ['type' => 'individual', 'description' => 'C', 'weightage' => 20, 'target' => 'T3'],
            ],
        ];

        $res2 = $this->actingAs($user)->post('/appraisal/submit-objective-setting', $bad);
        $res2->assertSessionHasErrors();
    }
}
