<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\Task\TaskSeeder;
use Database\Seeders\Grade\GradeSeeder;
use Database\Seeders\Project\ProjectSeeder;
use Database\Seeders\Position\PositionSeeder;
use Database\Seeders\TechStack\TechStackSeeder;
use Database\Seeders\JapaneseLevel\JapaneseLevelSeeder;
use Database\Seeders\Responsibility\ResponsibilitySeeder;
use Database\Seeders\ProficiencyLevel\ProficiencyLevelSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Only master/lookup data + the admin account are seeded.
     * Sample staff data (CsvDataSeeder, LocationSeeder) is no longer called.
     */
    public function run(): void
    {
        $this->call([
            ProjectSeeder::class,
            ResponsibilitySeeder::class,
            TechStackSeeder::class,
            ProficiencyLevelSeeder::class,
            TaskSeeder::class,
            PositionSeeder::class,
            GradeSeeder::class,
            JapaneseLevelSeeder::class,
            AdminSeeder::class,
            // CsvDataSeeder::class,   // sample staffs + related data (removed)
            // LocationSeeder::class,  // locations for staff 1..20 (removed)
        ]);
    }
}