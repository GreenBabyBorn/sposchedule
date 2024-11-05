<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

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
