<?php

namespace Database\Seeders\Staff;

use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffs = [
            [
                'eng_name' => 'John Doe',
                'jp_name' => 'ジョン・ドウ',
                'username' => 'johndoe',
                'password' => Hash::make('secret123'),
                'address' => 'Tokyo, Japan',
                'ph_number' => '09012345678',
                'position' => 'Developer',
                'role' => 'admin',
                'email' => 'john@example.com',
                'perment_date' => '2023-01-01',
                'ref_person' => 'Mr. Smith',
                'ref_ph_number' => '08098765432',
                'project' => 'Project A',
                'sort_key' => 1,
            ],
            [
                'eng_name' => 'Emily Tanaka',
                'jp_name' => 'エミリー・タナカ',
                'username' => 'emily',
                'password' => Hash::make('password456'),
                'address' => 'Osaka, Japan',
                'ph_number' => '08055554444',
                'position' => 'Designer',
                'role' => 'staff',
                'email' => 'emily@example.com',
                'perment_date' => '2024-03-15',
                'ref_person' => 'Ms. Yuki',
                'ref_ph_number' => '07033332222',
                'project' => 'Project B',
                'sort_key' => 2,
            ],
            [
                'eng_name' => 'Taro Suzuki',
                'jp_name' => '鈴木 太郎',
                'username' => 'taro',
                'password' => Hash::make('taro789'),
                'address' => 'Nagoya, Japan',
                'ph_number' => '07012344321',
                'position' => 'Manager',
                'role' => 'manager',
                'email' => 'taro@example.com',
                'perment_date' => '2022-06-30',
                'ref_person' => 'Mr. Ken',
                'ref_ph_number' => '09011112222',
                'project' => 'Project C',
                'sort_key' => 3,
            ],
        ];

        foreach ($staffs as $staff) {
            $staff['created_date'] = now();
            $staff['updated_date'] = now();
            Staff::create($staff);
        }
    }
}
