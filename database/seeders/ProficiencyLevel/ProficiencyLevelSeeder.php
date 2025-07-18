<?php

namespace Database\Seeders\ProficiencyLevel;

use App\Models\ProficiencyLevel;
use Illuminate\Database\Seeder;

class ProficiencyLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proficiencyLevels = [
            [
                'cd'   => '01',
                'name' => '最も得意',
                'abbv' => '★',
            ],
            [
                'cd'   => '02',
                'name' => '経験5年以上「最も得意」',
                'abbv' => '◎',
            ],
            [
                'cd'   => '03',
                'name' => '経験3年以上',
                'abbv' => '○',
            ],
            [
                'cd'   => '04',
                'name' => '経験1～2年',
                'abbv' => '□',
            ],
            [
                'cd'   => '05',
                'name' => '経験がある',
                'abbv' => '△',
            ],
        ];

        ProficiencyLevel::insert($proficiencyLevels);
    }
}
