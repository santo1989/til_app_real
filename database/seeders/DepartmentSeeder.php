<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        Department::create(['name' => 'Manufacturing']);
        Department::create(['name' => 'Sales']);
        Department::create(['name' => 'HR']);
    }
}
