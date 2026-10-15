<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Staff;
use Illuminate\Database\Seeder;

/**
 * Seeds only the admin account (row id=1 / role=1 in database/csvData/staffs.csv)
 * plus its location row. Password hash is taken from the CSV as-is.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $row = $this->adminRow();
        if (!$row) {
            $this->command?->warn('AdminSeeder: admin row (id=1) not found in csvData/staffs.csv');
            return;
        }

        $staff = Staff::create([
            'staff_no'       => $row['staff_no'],
            'eng_name'       => $row['eng_name'],
            'jp_name'        => $row['jp_name'],
            'username'       => $row['username'],
            'password'       => $row['password'],
            'address'        => $row['address'],
            'ph_number'      => $row['ph_number'],
            'position'       => $row['position'],
            'role'           => $row['role'],
            'email'          => $row['email'],
            'permanent_date' => $row['permanent_date'],
            'ref_person'     => $row['ref_person'],
            'sort_key'       => $row['sort_key'],
        ]);

        Location::insert([
            'staff_id'    => $staff->id,
            'lat'         => 0,
            'lon'         => 0,
            'allow_meter' => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    private function adminRow(): ?array
    {
        $path = database_path('csvData/staffs.csv');
        if (!is_readable($path) || ($h = fopen($path, 'r')) === false) {
            return null;
        }
        $header = fgetcsv($h, 10000, ',');
        while (($r = fgetcsv($h, 10000, ',')) !== false) {
            $row = array_combine($header, $r);
            if ((string) $row['id'] === '1') {
                fclose($h);
                return $row;
            }
        }
        fclose($h);
        return null;
    }
}