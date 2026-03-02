<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Objective;
use App\Models\FinancialYear;

class AppraisalBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_yearend_rating_thresholds(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($user);

        $fy = FinancialYear::create([
            'label' => '2025-26',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_active' => true,
        ]);
        $activeFY = $fy->label;

        $objectives = [];
        for ($i = 1; $i <= 4; $i++) {
            $objectives[] = Objective::create([
                'user_id' => $user->id,
                'type' => 'individual',
                'description' => "Objective {$i}",
                'weightage' => 25,
                'target' => "Target {$i}",
                'status' => 'set',
                'financial_year' => $activeFY,
                'created_by' => $user->id,
            ]);
        }

        $payload = [
            ['id' => $objectives[0]->id, 'score' => 85],
            ['id' => $objectives[1]->id, 'score' => 82],
            ['id' => $objectives[2]->id, 'score' => 80],
            ['id' => $objectives[3]->id, 'score' => 80],
        ];
        $resp = $this->post(route('appraisals.yearend.submit'), [
            'achievements' => $payload,
            'comments' => 'Test',
        ]);
        $resp->assertSessionDoesntHaveErrors();
        $resp->assertRedirect(route('appraisals.yearend'));
        // Outstanding: all >=80

        $payload = [
            ['id' => $objectives[0]->id, 'score' => 65],
            ['id' => $objectives[1]->id, 'score' => 62],
            ['id' => $objectives[2]->id, 'score' => 60],
            ['id' => $objectives[3]->id, 'score' => 60],
        ];
        $resp = $this->post(route('appraisals.yearend.submit'), [
            'achievements' => $payload,
            'comments' => 'Test',
        ]);
        $resp->assertSessionDoesntHaveErrors();
        $resp->assertRedirect(route('appraisals.yearend'));
        // Good: all >=60

        $payload = [
            ['id' => $objectives[0]->id, 'score' => 45],
            ['id' => $objectives[1]->id, 'score' => 42],
            ['id' => $objectives[2]->id, 'score' => 40],
            ['id' => $objectives[3]->id, 'score' => 40],
        ];
        $resp = $this->post(route('appraisals.yearend.submit'), [
            'achievements' => $payload,
            'comments' => 'Test',
        ]);
        $resp->assertSessionDoesntHaveErrors();
        $resp->assertRedirect(route('appraisals.yearend'));
        // Average: all >=40

        $payload = [
            ['id' => $objectives[0]->id, 'score' => 35],
            ['id' => $objectives[1]->id, 'score' => 32],
            ['id' => $objectives[2]->id, 'score' => 30],
            ['id' => $objectives[3]->id, 'score' => 30],
        ];
        $resp = $this->post(route('appraisals.yearend.submit'), [
            'achievements' => $payload,
            'comments' => 'Test',
        ]);
        $resp->assertSessionDoesntHaveErrors();
        $resp->assertRedirect(route('appraisals.yearend'));
        // Below: any <40
    }

    public function test_objective_revision_cutoff(): void
    {
        $this->seed();
        $user = User::where('role', 'employee')->first();
        $this->actingAs($user);

        // Simulate time after 9 months from FY start (2025-07-01 + 9 months = 2026-04-01)
        \Carbon\Carbon::setTestNow('2026-04-02');
        $payload = [
            'objectives' => [
                ['description' => 'Late objective', 'weightage' => 25, 'target' => 'X', 'is_smart' => true],
                ['description' => 'Late objective2', 'weightage' => 25, 'target' => 'Y', 'is_smart' => true],
                ['description' => 'Late objective3', 'weightage' => 25, 'target' => 'Z', 'is_smart' => true],
                ['description' => 'Late objective4', 'weightage' => 25, 'target' => 'W', 'is_smart' => true],
            ],
        ];
        $resp = $this->post(route('objectives.submit'), $payload);
        $resp->assertSessionHasErrors('objectives');
        \Carbon\Carbon::setTestNow(); // reset
    }
}
