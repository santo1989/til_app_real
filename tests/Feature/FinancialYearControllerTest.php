<?php

namespace Tests\Feature;

use App\Models\FinancialYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialYearControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_admin_can_create_financial_year()
    {
        // create an HR admin user (no factory in this project)
        $hr = User::create([
            'name' => 'HR Admin',
            'employee_id' => 'HR0001',
            'designation' => 'HR Manager',
            'email' => 'hr@example.test',
            'password' => bcrypt('secret'),
            'role' => 'hr_admin',
        ]);
        $this->actingAs($hr);

        $payload = [
            'label' => '2025-26',
            'start_date' => '2025-04-01',
            'end_date' => '2026-03-31',
            'is_active' => true,
        ];

        $resp = $this->post(route('financial-years.store'), $payload);
        $resp->assertRedirect(route('financial-years.index'));
        $this->assertDatabaseHas('financial_years', ['label' => '2025-26']);
    }
}
