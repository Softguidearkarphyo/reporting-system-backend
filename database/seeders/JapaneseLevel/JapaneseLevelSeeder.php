<?php

namespace Database\Seeders\JapaneseLevel;

use App\Models\JapaneseLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JapaneseLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $japaneseLevels = [
            [
                'name' => 'N1',
            ],
            [
                'name' => 'N2',
            ],
            [
                'name' => 'N3',
            ],
            [
                'name' => 'N4',
            ],
            [
                'name' => 'N5',
            ]
        ];

        JapaneseLevel::insert($japaneseLevels);
    }
}
