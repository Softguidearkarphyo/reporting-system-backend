<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\Leave\LeaveSeeder;
use Database\Seeders\Grade\GradeSeeder;
use Database\Seeders\JapaneseLevel\JapaneseLevelSeeder;
use Database\Seeders\Position\PositionSeeder;
use Database\Seeders\ProficiencyLevel\ProficiencyLevelSeeder;
use Database\Seeders\Project\ProjectSeeder;
use Database\Seeders\Responsibility\ResponsibilitySeeder;
use Database\Seeders\Staff\StaffSeeder;
use Database\Seeders\StaffProject\StaffProjectSeeder;
use Database\Seeders\Task\TaskSeeder;
use Database\Seeders\TechStack\TechStackSeeder;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            StaffSeeder::class,
            ProjectSeeder::class,
            StaffProjectSeeder::class,
            ResponsibilitySeeder::class,
            TechStackSeeder::class,
            ProficiencyLevelSeeder::class,
            TaskSeeder::class,
            PositionSeeder::class,
            GradeSeeder::class,
            JapaneseLevelSeeder::class,
        ]);
    }
}
