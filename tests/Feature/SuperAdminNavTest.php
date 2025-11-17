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

        // Assert presence of module links by href to avoid matching other labels like "Total Users"
        $response->assertSee('href="' . route('users.index') . '"', false);
        $response->assertSee('href="' . route('objectives.index') . '"', false);
        $response->assertSee('href="' . route('appraisals.index') . '"', false);
        $response->assertSee('href="' . route('idps.index') . '"', false);
        $response->assertSee('href="' . route('audit-logs.index') . '"', false);
        $response->assertSee('href="' . route('financial-years.index') . '"', false);
    }

    public function test_non_super_admin_does_not_see_admin_module_links()
    {
        $user = User::factory()->create(['role' => 'employee']);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);

        $response->assertDontSee('href="' . route('users.index') . '"', false);
        $response->assertDontSee('href="' . route('audit-logs.index') . '"', false);
        // Objectives are accessible as My Objectives for employees, but the main objectives index should not be shown
        $response->assertDontSee('href="' . route('objectives.index') . '"', false);
    }
}
