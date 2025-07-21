<?php

namespace Database\Seeders\Grade;

use App\Models\Grade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grade = [
            [
                'name' => 'A+',
            ],
            [
                'name' => 'A',
            ],
            [
                'name' => 'B',
            ]
        ];

        Grade::insert($grade);
    }
}
