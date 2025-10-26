<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Idp;

class IdpDeletionPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_hr_and_super_admin_can_delete_idp()
    {
        // create users
        $owner = User::create([
            'name' => 'Owner',
            'employee_id' => 'EMP' . uniqid(),
            'email' => 'owner@example.com',
            'password' => bcrypt('secret'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        $lineManager = User::create([
            'name' => 'Manager',
            'employee_id' => 'EMP' . uniqid(),
            'email' => 'manager@example.com',
            'password' => bcrypt('secret'),
            'role' => 'line_manager',
            'is_active' => true,
        ]);

        $hr = User::create([
            'name' => 'HR',
            'employee_id' => 'EMP' . uniqid(),
            'email' => 'hr@example.com',
            'password' => bcrypt('secret'),
            'role' => 'hr_admin',
            'is_active' => true,
        ]);

        $super = User::create([
            'name' => 'Super',
            'employee_id' => 'EMP' . uniqid(),
            'email' => 'super@example.com',
            'password' => bcrypt('secret'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $idp = Idp::create([
            'user_id' => $owner->id,
            'title' => 'Test IDP',
            'description' => 'desc',
        ]);

        // owner cannot delete
        $this->assertFalse($owner->can('delete', $idp));

        // line manager cannot delete
        $this->assertFalse($lineManager->can('delete', $idp));

        // hr can delete
        $this->assertTrue($hr->can('delete', $idp));

        // super can delete
        $this->assertTrue($super->can('delete', $idp));
    }
}
