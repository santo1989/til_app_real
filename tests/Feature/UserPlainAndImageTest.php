<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\User;

class UserPlainAndImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_plain_is_encrypted_in_db_and_decrypted_on_access()
    {
        $user = User::create([
            'name' => 'Plain Test',
            'employee_id' => 'EMP' . uniqid(),
            'email' => 'plain@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'employee',
            'is_active' => true,
            'password_plain' => 'my-plain-pass-XYZ'
        ]);

        // raw attribute should not equal the plain text
        $raw = $user->getAttributes()['password_plain'] ?? null;
        $this->assertNotNull($raw);
        $this->assertNotEquals('my-plain-pass-XYZ', $raw);

        // accessor should decrypt and return original
        $this->assertEquals('my-plain-pass-XYZ', $user->password_plain);
    }

    public function test_user_image_upload_stores_file_and_db_path()
    {
        Storage::fake('public');

        // create an HR admin to act as
        $admin = User::create([
            'name' => 'HR Admin',
            'employee_id' => 'EMP' . uniqid(),
            'email' => 'hr@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'hr_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->post(route('users.store'), [
            'name' => 'New User',
            'employee_id' => 'EMP' . uniqid(),
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'employee',
            'user_image' => $file,
        ]);

        $response->assertRedirect();

        // Assert the file was stored
        $this->assertTrue(Storage::disk('public')->exists('user_images/' . $file->hashName()));

        // Assert DB has the path for the created user
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);

        $created = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($created->user_image);
        $this->assertTrue(Storage::disk('public')->exists($created->user_image));
    }
}
