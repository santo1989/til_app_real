<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Appraisal;
use App\Models\FinancialYear;

class SignatureOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_cannot_sign_before_manager()
    {
        // create manager (line manager) and employee
        $manager = User::create([
            'name' => 'Manager',
            'employee_id' => 'M100',
            'email' => 'mgr@example.com',
            'password' => bcrypt('secret'),
            'role' => 'line_manager',
        ]);

        $employee = User::create([
            'name' => 'Employee',
            'employee_id' => 'E100',
            'email' => 'emp@example.com',
            'password' => bcrypt('secret'),
            'role' => 'employee',
            'line_manager_id' => $manager->id,
        ]);

        // create active FY
        FinancialYear::create([
            'label' => '2025-26',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        // create appraisal for employee
        $app = Appraisal::create([
            'user_id' => $employee->id,
            'type' => 'midterm',
            'date' => now(),
            'financial_year' => '2025-26',
            'status' => 'in_progress',
            'conducted_by' => $manager->id,
        ]);

        // Using an HR admin to perform the sign attempts (HR can sign but order should be enforced)
        $hr = User::create([
            'name' => 'HR',
            'employee_id' => 'HR1',
            'email' => 'hr@example.com',
            'password' => bcrypt('secret'),
            'role' => 'hr_admin',
        ]);

        // HR attempts to sign as supervisor BEFORE manager signs -> should be blocked by order enforcement
        $resp = $this->actingAs($hr)->post('/appraisal/appraisals/' . $app->id . '/sign', ['role' => 'supervisor', 'name' => 'HR Sign']);
        // debug: dump response code and content to storage log (temporary)
        file_put_contents(storage_path('logs/sig_debug.json'), json_encode([
            'step' => 'supervisor-before-manager',
            'status' => $resp->getStatusCode(),
            'content' => $resp->getContent(),
        ]) . PHP_EOL, FILE_APPEND);
        $this->assertTrue(in_array($resp->getStatusCode(), [302, 403]));
        // ensure the appraisal was not marked signed_by_manager
        $app->refresh();
        file_put_contents(storage_path('logs/sig_debug.json'), json_encode([
            'step' => 'app-after-supervisor-attempt',
            'app' => $app->toArray(),
        ]) . PHP_EOL, FILE_APPEND);
        $this->assertFalse((bool)$app->signed_by_manager);

        // Manager signs properly as manager (acting as HR admin signing manager role is permitted)
        $resp2 = $this->actingAs($hr)->post('/appraisal/appraisals/' . $app->id . '/sign', ['role' => 'manager', 'name' => 'HR Manager Sign']);
        $resp2->assertRedirect();

        $app->refresh();
        $this->assertTrue((bool)$app->signed_by_manager);

        // Now signing as supervisor should succeed (manager already signed)
        $resp3 = $this->actingAs($hr)->post('/appraisal/appraisals/' . $app->id . '/sign', ['role' => 'supervisor', 'name' => 'Supervisor Sign']);
        $resp3->assertRedirect();
        $app->refresh();
        $this->assertTrue((bool)$app->signed_by_supervisor);
    }
}
