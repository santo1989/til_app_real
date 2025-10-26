<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class ObjectiveValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_cannot_submit_objectives_with_invalid_weightage_total(): void
    {
        $this->seed();
        $user = User::where('role', 'employee')->first();
        $this->actingAs($user);

        $payload = [
            'objectives' => [
                ['description' => 'A', 'weightage' => 25, 'target' => 'X', 'is_smart' => true],
                ['description' => 'B', 'weightage' => 25, 'target' => 'Y', 'is_smart' => true],
                ['description' => 'C', 'weightage' => 25, 'target' => 'Z', 'is_smart' => true],
            ],
        ];

        $resp = $this->post(route('objectives.submit'), $payload);
        $resp->assertSessionHasErrors('objectives');
    }

    public function test_guest_redirected_from_appraisal_routes(): void
    {
        $resp = $this->get('/appraisal/my-objectives');
        $resp->assertRedirect('/login');
    }
}
