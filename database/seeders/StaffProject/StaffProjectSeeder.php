<?php

namespace Database\Seeders\StaffProject;

use App\Models\StaffProject;
use Illuminate\Database\Seeder;

class StaffProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffProjects = [
            [
                'staff_id' => 1,
                'project_id' => 1
            ],
            [
                'staff_id' => 2,
                'project_id' => 2
            ],
            [
                'staff_id' => 3,
                'project_id' => 3
            ],
        ];

        StaffProject::insert($staffProjects);
    }
}
