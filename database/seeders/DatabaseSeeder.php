<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            SuperAdminSeeder::class, // Create super admin first
            DepartmentSeeder::class,
            UserSeeder::class,
            ObjectiveSeeder::class,
            AppraisalSeeder::class,
            IdpSeeder::class,
            UsersAndDataSeeder::class
        ]);
    }
}
