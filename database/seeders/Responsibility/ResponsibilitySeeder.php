<?php

namespace Database\Seeders\Responsibility;

use App\Models\Responsibility;
use Illuminate\Database\Seeder;

class ResponsibilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $responsibilities = [
            [
                'name' => '実装',
            ],
            [
                'name' => '設計',
            ]
        ];

        foreach ($responsibilities as $responsibility) {
            $responsibility['created_at'] = now();
            $responsibility['updated_at'] = now();
            Responsibility::create($responsibility);
        }
    }
}
