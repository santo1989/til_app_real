<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_admin_module_links()
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);

        // Assert presence of appraisal-module links by href to avoid matching other labels
        $response->assertSee('href="' . route('objectives.my') . '"', false);
        $response->assertSee('href="' . route('appraisals.midterm') . '"', false);
        $response->assertSee('href="' . route('objectives.team') . '"', false);
        $response->assertSee('href="' . route('objectives.approvals') . '"', false);
        $response->assertSee('href="' . route('idps.index', ['manager_id' => $user->id]) . '"', false);
        $response->assertSee('href="' . route('objectives.department') . '"', false);
        $response->assertSee('href="' . route('objectives.board.index') . '"', false);
        $response->assertSee('href="' . route('idps.index') . '"', false);
    }

    public function test_non_super_admin_does_not_see_admin_module_links()
    {
        $user = User::factory()->create(['role' => 'employee']);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);

        $response->assertDontSee('href="' . route('objectives.team') . '"', false);
        $response->assertDontSee('href="' . route('objectives.approvals') . '"', false);
        $response->assertDontSee('href="' . route('objectives.department') . '"', false);
        $response->assertDontSee('href="' . route('objectives.board.index') . '"', false);
    }
}
