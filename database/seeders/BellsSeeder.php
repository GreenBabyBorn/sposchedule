<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bell;
use App\Models\BellsPeriod;

class BellsSeeder extends Seeder
{
    public function run()
    {
        // Дни недели
        $weekDays = ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'];

        // Корпуса
        $buildings = range(1, 6);

        // Расписание звонков
        $schedules = [
            0 => [
                ['period_from' => '08:00', 'period_to' => '08:55', 'has_break' => false],
            ],
            1 => [
                ['period_from' => '09:00', 'period_to' => '09:45', 'has_break' => true, 'period_from_after' => '09:50', 'period_to_after' => '10:35'],
            ],
            2 => [
                ['period_from' => '10:50', 'period_to' => '11:35', 'has_break' => true, 'period_from_after' => '11:40', 'period_to_after' => '12:25'],
            ],
            3 => [
                ['period_from' => '12:45', 'period_to' => '13:30', 'has_break' => true, 'period_from_after' => '13:35', 'period_to_after' => '14:20'],
            ],
            4 => [
                ['period_from' => '14:30', 'period_to' => '15:15', 'has_break' => true, 'period_from_after' => '15:20', 'period_to_after' => '16:05'],
            ],
            5 => [
                ['period_from' => '16:15', 'period_to' => '17:00', 'has_break' => true, 'period_from_after' => '17:05', 'period_to_after' => '17:50'],
            ],
        ];

        // Создание звонков для каждого корпуса и каждого дня недели
        foreach ($buildings as $building) {
            foreach ($weekDays as $weekDay) {
                // Создаем звонок
                $bell = Bell::create([
                    'type' => 'main',
                    'week_day' => $weekDay,
                    'date' => null,
                    'building' => $building,
                    'published' => true,
                ]);

                // Создание периодов для каждого звонка
                foreach ($schedules as $index => $periods) {
                    foreach ($periods as $period) {
                        BellsPeriod::create([
                            'bells_id' => $bell->id,
                            'index' => $index,
                            'has_break' => $period['has_break'],
                            'period_from' => $period['period_from'],
                            'period_to' => $period['period_to'],
                            'period_from_after' => $period['has_break'] ? $period['period_from_after'] : null,
                            'period_to_after' => $period['has_break'] ? $period['period_to_after'] : null,
                        ]);
                    }
                }
            }
        }
    }
}
