<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Objective;

class AppraisalBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_yearend_rating_thresholds(): void
    {
        $this->seed();
        $user = User::where('role', 'employee')->first();
        $this->actingAs($user);

        $objectives = [
            ['score' => 85, 'weight' => 25],
            ['score' => 82, 'weight' => 25],
            ['score' => 80, 'weight' => 25],
            ['score' => 80, 'weight' => 25],
        ];
        $resp = $this->post(route('appraisals.yearend.submit'), [
            'achievements' => $objectives,
            'comments' => 'Test',
        ]);
        $resp->assertSessionDoesntHaveErrors();
        $resp->assertRedirect(route('appraisals.yearend'));
        // Outstanding: all >=80

        $objectives = [
            ['score' => 65, 'weight' => 25],
            ['score' => 62, 'weight' => 25],
            ['score' => 60, 'weight' => 25],
            ['score' => 60, 'weight' => 25],
        ];
        $resp = $this->post(route('appraisals.yearend.submit'), [
            'achievements' => $objectives,
            'comments' => 'Test',
        ]);
        $resp->assertSessionDoesntHaveErrors();
        $resp->assertRedirect(route('appraisals.yearend'));
        // Good: all >=60

        $objectives = [
            ['score' => 45, 'weight' => 25],
            ['score' => 42, 'weight' => 25],
            ['score' => 40, 'weight' => 25],
            ['score' => 40, 'weight' => 25],
        ];
        $resp = $this->post(route('appraisals.yearend.submit'), [
            'achievements' => $objectives,
            'comments' => 'Test',
        ]);
        $resp->assertSessionDoesntHaveErrors();
        $resp->assertRedirect(route('appraisals.yearend'));
        // Average: all >=40

        $objectives = [
            ['score' => 35, 'weight' => 25],
            ['score' => 32, 'weight' => 25],
            ['score' => 30, 'weight' => 25],
            ['score' => 30, 'weight' => 25],
        ];
        $resp = $this->post(route('appraisals.yearend.submit'), [
            'achievements' => $objectives,
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
