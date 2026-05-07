<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            [
                'name' => 'Development',
                'receiver_email' => 'bhushanmahajan20018@gmail.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'QA',
                'receiver_email' => 'bhushanamahajan.autoappy@gmail.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
       ]);
    }
}