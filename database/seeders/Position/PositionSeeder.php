<?php

namespace Database\Seeders\Position;

use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'name' => 'PM',
            ],
            [
                'name' => 'SE',
            ],
            [
                'name' => '研修',
            ]
        ];

        Position::insert($positions);
    }
}
