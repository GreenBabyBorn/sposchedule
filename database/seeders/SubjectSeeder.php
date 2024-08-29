<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subject::create(['name' => 'Математика']);
        Subject::create(['name' => 'Физика']);
        Subject::create(['name' => 'Химия']);
        Subject::create(['name' => 'Информатика']);
        Subject::create(['name' => 'Литература']);
    }
}
