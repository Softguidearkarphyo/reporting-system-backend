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
                'name' => '',
                'abbv' => '-',
            ],
            [
                'name' => '最も得意',
                'abbv' => '★',
            ],
            [
                'name' => '',
                'abbv' => '◎★',
            ],
            [
                'name' => '',
                'abbv' => '○★',
            ],
            [
                'name' => '',
                'abbv' => '□★',
            ],
            [
                'name' => '',
                'abbv' => '△★',
            ],
            [
                'name' => '経験5年以上「最も得意」',
                'abbv' => '◎',
            ],
            [
                'name' => '経験3年以上',
                'abbv' => '○',
            ],
            [
                'name' => '経験1～2年',
                'abbv' => '□',
            ],
            [
                'name' => '経験がある',
                'abbv' => '△',
            ],
        ];

        ProficiencyLevel::insert($proficiencyLevels);
    }
}
