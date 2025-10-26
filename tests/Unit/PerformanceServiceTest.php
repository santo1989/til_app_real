<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Objective;
use App\Services\PerformanceService;

class PerformanceServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_assigns_outstanding_when_total_ge_90_and_each_individual_ge_80()
    {
        $user = User::factory()->create();
        $fyLabel = '2025-26';

        // 3 individual objectives, each achieved 90, weights sum to 100 -> total 90
        $weights = [40, 30, 30];
        foreach ($weights as $i => $w) {
            Objective::create([
                'user_id' => $user->id,
                'type' => 'individual',
                'description' => 'Ind objective ' . $i,
                'weightage' => $w,
                'target' => 'Do X',
                'financial_year' => $fyLabel,
                'target_achieved' => 90,
            ]);
        }

        $svc = new PerformanceService();
        $result = $svc->computeUserScores($user->id, $fyLabel);

        $this->assertEquals('Outstanding', $result['status']);
        $this->assertEquals(90.0, $result['total_score']);
    }

    /** @test */
    public function it_assigns_excellent_when_total_in_80_89_range_or_outstanding_condition_not_met()
    {
        $user = User::factory()->create();
        $fyLabel = '2026-27';

        // Make total 85 (e.g., all objectives 85)
        $weights = [50, 50];
        foreach ($weights as $i => $w) {
            Objective::create([
                'user_id' => $user->id,
                'type' => 'individual',
                'description' => 'Ind objective ' . $i,
                'weightage' => $w,
                'target' => 'Do Y',
                'financial_year' => $fyLabel,
                'target_achieved' => 85,
            ]);
        }

        $svc = new PerformanceService();
        $result = $svc->computeUserScores($user->id, $fyLabel);
        $this->assertEquals('Excellent', $result['status']);

        // Also test a high total that fails outstanding per-individual minima -> downgrade to Excellent
        $user2 = User::factory()->create();
        $fy2Label = '2027-28';

        // total >= 90 but one individual is below 80 -> should be downgraded to Excellent
        Objective::create([
            'user_id' => $user2->id,
            'type' => 'individual',
            'description' => 'High A',
            'weightage' => 50,
            'target' => 'T1',
            'financial_year' => $fy2Label,
            'target_achieved' => 95,
        ]);
        Objective::create([
            'user_id' => $user2->id,
            'type' => 'individual',
            'description' => 'Lower B',
            'weightage' => 50,
            'target' => 'T2',
            'financial_year' => $fy2Label,
            'target_achieved' => 75, // below the 80 per-individual outstanding requirement
        ]);

        $result2 = $svc->computeUserScores($user2->id, $fy2Label);
        $this->assertEquals('Excellent', $result2['status']);
    }

    /** @test */
    public function it_assigns_good_only_when_total_in_70_79_and_every_objective_ge_60()
    {
        $user = User::factory()->create();
        $fyLabel = '2028-29';

        // Make total 75 and all objectives >= 60
        Objective::create([
            'user_id' => $user->id,
            'type' => 'departmental',
            'description' => 'Dept A',
            'weightage' => 50,
            'target' => 'T1',
            'financial_year' => $fyLabel,
            'target_achieved' => 70,
        ]);
        Objective::create([
            'user_id' => $user->id,
            'type' => 'individual',
            'description' => 'Ind B',
            'weightage' => 50,
            'target' => 'T2',
            'financial_year' => $fyLabel,
            'target_achieved' => 80,
        ]);

        $svc = new PerformanceService();
        $result = $svc->computeUserScores($user->id, $fyLabel);
        $this->assertEquals('Good', $result['status']);

        // If any objective below 60, downgrade to Average even if total in 70s
        $user2 = User::factory()->create();
        $fy2Label = '2029-30';

        Objective::create([
            'user_id' => $user2->id,
            'type' => 'departmental',
            'description' => 'Dept Low',
            'weightage' => 50,
            'target' => 'T1',
            'financial_year' => $fy2Label,
            'target_achieved' => 55, // below 60
        ]);
        Objective::create([
            'user_id' => $user2->id,
            'type' => 'individual',
            'description' => 'Ind High',
            'weightage' => 50,
            'target' => 'T2',
            'financial_year' => $fy2Label,
            'target_achieved' => 95,
        ]);

        $result2 = $svc->computeUserScores($user2->id, $fy2Label);
        $this->assertEquals('Average', $result2['status']);
    }

    /** @test */
    public function it_assigns_average_and_below_average_based_on_total_ranges()
    {
        $user = User::factory()->create();
        $fyLabel = '2030-31';

        // Average case: total 65
        Objective::create([
            'user_id' => $user->id,
            'type' => 'individual',
            'description' => 'One',
            'weightage' => 100,
            'target' => 'T',
            'financial_year' => $fyLabel,
            'target_achieved' => 65,
        ]);

        $svc = new PerformanceService();
        $result = $svc->computeUserScores($user->id, $fyLabel);
        $this->assertEquals('Average', $result['status']);

        // Below Average case: total 55
        $user2 = User::factory()->create();
        $fy2Label = '2031-32';
        Objective::create([
            'user_id' => $user2->id,
            'type' => 'individual',
            'description' => 'Low',
            'weightage' => 100,
            'target' => 'T',
            'financial_year' => $fy2Label,
            'target_achieved' => 55,
        ]);

        $result2 = $svc->computeUserScores($user2->id, $fy2Label);
        $this->assertEquals('Below Average', $result2['status']);
    }
}
