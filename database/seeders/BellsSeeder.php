<?php

namespace Database\Seeders;

use App\Models\Bell;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BellsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = ['main', 'changes'];
        $variants = ['normal', 'reduced'];
        $weekDays = ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС']; // Дни недели
        $buildings = range(1, 6); // Значения для building от 1 до 6

        foreach ($types as $type) {
            foreach ($variants as $variant) {
                foreach ($weekDays as $week_day) {
                    foreach ($buildings as $building) {
                        // Устанавливаем день недели только для типа "main"
                        $currentWeekDay = $type === 'main' ? $week_day : null;

                        // Проверяем на существование записи перед созданием
                        $existingBell = Bell::where('variant', $variant)
                            ->where('week_day', $currentWeekDay)
                            ->where('building', $building)
                            ->exists();

                        if (!$existingBell) {
                            Bell::create([
                                'type' => $type,
                                'variant' => $variant,
                                'week_day' => $currentWeekDay,
                                'building' => $building,
                            ]);
                        }
                    }
                }

                // Для типа "changes", создаем записи без week_day
                if ($type === 'changes') {
                    foreach ($buildings as $building) {
                        $existingBell = Bell::where('variant', $variant)
                            ->whereNull('week_day')
                            ->where('building', $building)
                            ->exists();

                        if (!$existingBell) {
                            Bell::create([
                                'type' => $type,
                                'variant' => $variant,
                                'week_day' => null,
                                'building' => $building,
                            ]);
                        }
                    }
                }
            }
        }
    }



}