<?php

namespace Database\Seeders\Location;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lat = 16.8558592;
        $lng = 96.1576960;

        for ($i = 1; $i <= 20; $i++) {
            Location::insert([
                'staff_id' => $i,
                'lat'      => $lat,
                'lng'      => $lng,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
