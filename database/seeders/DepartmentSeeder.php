<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Development',
                'receiver_email' => 'bhushanmahajan20018@gmail.com',
            ],
            [
                'name' => 'Testing',
                'receiver_email' => 'bhushanmahajan.autoappy@gmail.com',
            ]
        ];

        foreach ($departments as $department) {

            Department::firstOrCreate(
                [
                    'name' => $department['name'],
                ],
                [
                    'receiver_email' => $department['receiver_email'],
                ]
            );
        }
    }
}