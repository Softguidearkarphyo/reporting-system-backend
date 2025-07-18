<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\ProficiencyLevel;
use Illuminate\Database\Seeder;
use Database\Seeders\Staff\StaffSeeder;
use Database\Seeders\Project\ProjectSeeder;
use Database\Seeders\StaffProject\StaffProjectSeeder;
use Database\Seeders\Responsibility\ResponsibilitySeeder;
use Database\Seeders\TechStack\TechStackSeeder;
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
            StaffSeeder::class,
            ProjectSeeder::class,
            StaffProjectSeeder::class,
            ResponsibilitySeeder::class,
            TechStackSeeder::class,
            ProficiencyLevelSeeder::class,
        ]);
    }
}
