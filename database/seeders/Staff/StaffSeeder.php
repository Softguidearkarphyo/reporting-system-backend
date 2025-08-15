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
                'staff_no' => 'SGWT0000000000000001',
                'eng_name' => 'John Doe',
                'jp_name' => 'ジョン・ドウ',
                'username' => 'johndoe',
                'password' => Hash::make('secret123'),
                'address' => 'Tokyo, Japan',
                'ph_number' => '09012345678',
                'position' => 1,
                'role' => 1,
                'email' => 'john@example.com',
                'permanent_date' => '2023-01-01',
                'ref_person' => 'Mr. Smith',
                'ref_ph_number' => '08098765432',
                'sort_key' => 1,
            ],
            [
                'staff_no' => 'SGWT0000000000000001',
                'eng_name' => 'Emily Tanaka',
                'jp_name' => 'エミリー・タナカ',
                'username' => 'emily',
                'password' => Hash::make('secret123'),
                'address' => 'Osaka, Japan',
                'ph_number' => '08055554444',
                'position' => 2,
                'role' => 2,
                'email' => 'emily@example.com',
                'permanent_date' => '2025-03-15',
                'ref_person' => 'Ms. Yuki',
                'ref_ph_number' => '07033332222',
                'sort_key' => 2,
            ],
            [
                'staff_no' => 'SGWT0000000000000001',
                'eng_name' => 'Taro Suzuki',
                'jp_name' => '鈴木 太郎',
                'username' => 'taro',
                'password' => Hash::make('secret123'),
                'address' => 'Nagoya, Japan',
                'ph_number' => '07012344321',
                'position' => 3,
                'role' => 2,
                'email' => 'taro@example.com',
                'permanent_date' => '2025-01-01',
                'ref_person' => 'Mr. Ken',
                'ref_ph_number' => '09011112222',
                'sort_key' => 3,
            ],
            [
                'staff_no' => 'SGWT0000000000000001',
                'eng_name' => 'Mai',
                'jp_name' => '鈴木',
                'username' => 'mai',
                'password' => Hash::make('secret123'),
                'address' => 'Nagoya, Japan',
                'ph_number' => '07012344321',
                'position' => 3,
                'role' => 2,
                'email' => 'mai@example.com',
                'permanent_date' => '2022-06-30',
                'ref_person' => 'Mr. Ken',
                'ref_ph_number' => '09011112222',
                'sort_key' => 3,
            ],
            [
                'staff_no' => 'SGWT0000000000000001',
                'eng_name' => 'Mei',
                'jp_name' => '太郎',
                'username' => 'mei',
                'password' => Hash::make('secret123'),
                'address' => 'Nagoya, Japan',
                'ph_number' => '07012344321',
                'position' => 3,
                'role' => 2,
                'email' => 'taro@example.com',
                'permanent_date' => '2022-06-30',
                'ref_person' => 'Mr. Ken',
                'ref_ph_number' => '09011112222',
                'sort_key' => 3,
            ],
            [
                'staff_no' => 'SGWT0000000000000001',
                'eng_name' => 'YuKo',
                'jp_name' => '郎郎',
                'username' => 'yuko',
                'password' => Hash::make('secret123'),
                'address' => 'Nagoya, Japan',
                'ph_number' => '07012344321',
                'position' => 3,
                'role' => 2,
                'email' => 'taro@example.com',
                'permanent_date' => '2022-06-30',
                'ref_person' => 'Mr. Ken',
                'ref_ph_number' => '09011112222',
                'sort_key' => 3,
            ],
            [
                'staff_no' => 'SGWT0000000000000001',
                'eng_name' => 'Yuki',
                'jp_name' => '郎太',
                'username' => 'yuki',
                'password' => Hash::make('secret123'),
                'address' => 'Nagoya, Japan',
                'ph_number' => '07012344321',
                'position' => 3,
                'role' => 2,
                'email' => 'taro@example.com',
                'permanent_date' => '2022-06-30',
                'ref_person' => 'Mr. Ken',
                'ref_ph_number' => '09011112222',
                'sort_key' => 3,
            ],
            [
                'staff_no' => 'SGWT0000000000000001',
                'eng_name' => 'YuJi',
                'jp_name' => '太太',
                'username' => 'yuji',
                'password' => Hash::make('secret123'),
                'address' => 'Nagoya, Japan',
                'ph_number' => '07012344321',
                'position' => 3,
                'role' => 2,
                'email' => 'taro@example.com',
                'permanent_date' => '2022-06-30',
                'ref_person' => 'Mr. Ken',
                'ref_ph_number' => '09011112222',
                'sort_key' => 3,
            ]
        ];

        Staff::insert($staffs);
    }
}
