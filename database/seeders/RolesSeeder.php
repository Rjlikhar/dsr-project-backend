<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
         Role::firstOrCreate([
            'name' => 'Admin',
        ]);

        Role::firstOrCreate([
            'name' => 'Employee',
        ]);

        Role::firstOrCreate([
            'name' => 'Mentor',
        ]);

        Role::firstOrCreate([
            'name' => 'Reviewer',
        ]);
    }
}