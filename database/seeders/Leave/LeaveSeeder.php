<?php

namespace Database\Seeders\Leave;

use App\Models\Leave;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaves = [
            [
                'staff_id' => 1,
                'leave_type' => 'Sick Leave',
                'start_date' => '2023-01-01',
                'end_date' => '2023-01-04',
                'reason' => 'Trip leave',
            ],
        ];

        Leave::insert($leaves);
    }
}
