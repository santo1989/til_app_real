<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\FinancialYear;

class FinancialYearLabelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_financial_year_using_label()
    {
        $user = User::factory()->create(['role' => 'hr_admin']);

        $this->actingAs($user)
            ->post('/financial-years', [
                'label' => '2025-26',
                'start_date' => now()->startOfYear()->toDateString(),
                'end_date' => now()->endOfYear()->toDateString(),
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('financial_years', [
            'label' => '2025-26',
        ]);
    }

    /** @test */
    public function it_updates_a_financial_year_label()
    {
        $user = User::factory()->create(['role' => 'hr_admin']);

        $fy = FinancialYear::create([
            'label' => '2024-25',
            'start_date' => now()->subYear()->startOfYear()->toDateString(),
            'end_date' => now()->subYear()->endOfYear()->toDateString(),
        ]);

        $this->actingAs($user)
            ->put('/financial-years/' . $fy->id, [
                'label' => '2024-25-updated',
                'start_date' => $fy->start_date->toDateString(),
                'end_date' => $fy->end_date->toDateString(),
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('financial_years', [
            'id' => $fy->id,
            'label' => '2024-25-updated',
        ]);
    }
}
