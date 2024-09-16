<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Building;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Building::create([
            'name' => '1',
            'location' => 'Циолковского, 19',
        ]);
        Building::create([
            'name' => '2',
            'location' => 'Куйбышевское шоссе, 18 к2',
        ]);
        Building::create([
            'name' => '3',
            'location' => 'Куйбышевское шоссе, 18 к1',
        ]);
        Building::create([
            'name' => '4',
            'location' => 'Куйбышевское шоссе, 18',
        ]);
        Building::create([
            'name' => '5',
            'location' => 'Куйбышевское шоссе, 16',
        ]);
        Building::create([
            'name' => '6',
            'location' => 'Бирюзова, 2',
        ]);
    }
}
