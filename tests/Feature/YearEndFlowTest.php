<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Objective;
use App\Models\FinancialYear;
use App\Models\Appraisal;
use App\Models\Pip;

class YearEndFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Exercise the year-end conduct flow end-to-end and assert rating normalization
     * and PIP creation for below-average performers.
     */
    public function test_year_end_flow_creates_appraisal_and_pip_and_normalizes_rating()
    {
        // Create active financial year
        $fy = FinancialYear::create([
            'label' => 'FY2025',
            'start_date' => now()->subMonths(6)->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'is_active' => true,
        ]);

        // Create a line manager (the actor)
        $manager = User::factory()->create(['role' => 'line_manager']);

        // Employee 1: low scores -> expect PIP and rating normalized to 'below'
        $employee1 = User::factory()->create(['role' => 'employee', 'line_manager_id' => $manager->id]);

        // Create three objectives summing roughly to 100 weightage
        $o1 = Objective::create(['user_id' => $employee1->id, 'type' => 'individual', 'description' => 'Obj 1', 'weightage' => 34, 'financial_year' => $fy->label, 'created_by' => $manager->id]);
        $o2 = Objective::create(['user_id' => $employee1->id, 'type' => 'individual', 'description' => 'Obj 2', 'weightage' => 33, 'financial_year' => $fy->label, 'created_by' => $manager->id]);
        $o3 = Objective::create(['user_id' => $employee1->id, 'type' => 'individual', 'description' => 'Obj 3', 'weightage' => 33, 'financial_year' => $fy->label, 'created_by' => $manager->id]);

        // Submit low achievement scores (50% each) -> total below 60
        $this->actingAs($manager)
            ->post(route('appraisals.conduct_yearend.submit', ['user_id' => $employee1->id]), [
                'achievements' => [
                    ['id' => $o1->id, 'score' => 50, 'rating' => 0],
                    ['id' => $o2->id, 'score' => 50, 'rating' => 0],
                    ['id' => $o3->id, 'score' => 50, 'rating' => 0],
                ],
                'supervisor_comments' => 'Low performance',
            ]);

        $appraisal1 = Appraisal::where('user_id', $employee1->id)->where('financial_year', $fy->label)->first();
        $this->assertNotNull($appraisal1, 'Appraisal should be created for employee1');
        $this->assertEquals('below', $appraisal1->rating, 'Rating should be normalized to DB token "below" for low performer');

        $pip = Pip::where('user_id', $employee1->id)->where('appraisal_id', $appraisal1->id)->first();
        $this->assertNotNull($pip, 'PIP should be created for below-average performer');

        // Employee 2: high scores -> expect outstanding normalized to 'outstanding'
        $employee2 = User::factory()->create(['role' => 'employee', 'line_manager_id' => $manager->id]);
        $p1 = Objective::create(['user_id' => $employee2->id, 'type' => 'individual', 'description' => 'O1', 'weightage' => 50, 'financial_year' => $fy->label, 'created_by' => $manager->id]);
        $p2 = Objective::create(['user_id' => $employee2->id, 'type' => 'individual', 'description' => 'O2', 'weightage' => 50, 'financial_year' => $fy->label, 'created_by' => $manager->id]);

        // Submit high achievement scores (95% each) -> total >=90 and individual >=80 -> Outstanding
        $this->actingAs($manager)
            ->post(route('appraisals.conduct_yearend.submit', ['user_id' => $employee2->id]), [
                'achievements' => [
                    ['id' => $p1->id, 'score' => 95, 'rating' => 0],
                    ['id' => $p2->id, 'score' => 95, 'rating' => 0],
                ],
                'supervisor_comments' => 'Excellent work',
            ]);

        $appraisal2 = Appraisal::where('user_id', $employee2->id)->where('financial_year', $fy->label)->first();
        $this->assertNotNull($appraisal2, 'Appraisal should be created for employee2');
        $this->assertEquals('outstanding', $appraisal2->rating, 'Rating should be normalized to DB token "outstanding" for outstanding performer');
    }
}
