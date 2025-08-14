<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\CsvDataSeeder;
use Database\Seeders\Task\TaskSeeder;
use Database\Seeders\Grade\GradeSeeder;
use Database\Seeders\Project\ProjectSeeder;
use Database\Seeders\Location\LocationSeeder;
use Database\Seeders\Position\PositionSeeder;
use Database\Seeders\TechStack\TechStackSeeder;
use Database\Seeders\JapaneseLevel\JapaneseLevelSeeder;
use Database\Seeders\Responsibility\ResponsibilitySeeder;
use Database\Seeders\ProficiencyLevel\ProficiencyLevelSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            ProjectSeeder::class,
            ResponsibilitySeeder::class,
            TechStackSeeder::class,
            ProficiencyLevelSeeder::class,
            TaskSeeder::class,
            PositionSeeder::class,
            GradeSeeder::class,
            JapaneseLevelSeeder::class,
            CsvDataSeeder::class,
            LocationSeeder::class,
        ]);
    }
}
