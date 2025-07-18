<?php

namespace Database\Seeders\TechStack;

use App\Models\TechStack;
use Illuminate\Database\Seeder;

class TechStackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $techStacks = [
            ['name' => 'JAVA'],
            ['name' => 'C#'],
            ['name' => 'PHP'],
            ['name' => 'C++'],
            ['name' => 'ASP.Net'],
            ['name' => 'Access'],
            ['name' => 'RoR'],
            ['name' => 'ColdFusion'],
            ['name' => 'VB6'],
            ['name' => 'HTML5'],
            ['name' => 'JavaScript'],
            ['name' => 'Laravel'],
            ['name' => 'jquery'],
            ['name' => 'bootstrap'],
            ['name' => 'Python'],
            ['name' => 'Angular'],
            ['name' => 'Vue'],
            ['name' => 'Nuxt'],
            ['name' => 'React'],
            ['name' => 'Next'],
            ['name' => 'Redis'],
            ['name' => 'AWS S3'],
            ['name' => 'Postgres'],
            ['name' => 'DB2'],
            ['name' => 'mySQL'],
            ['name' => 'Oracle'],
        ];

        foreach ($techStacks as $techStack) {
            $techStack['created_at'] = now();
            $techStack['updated_at'] = now();
            TechStack::create($techStack);
        }
    }
}
