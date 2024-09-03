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

        foreach ($types as $type) {
            foreach ($variants as $variant) {
                for ($i = 0; $i < 7; $i++) {
                    // Если тип 'main', используем дни недели, если 'changes', используем даты
                    $week_day = $type === 'main' ? $weekDays[$i] : null;
                    // $date = $type === 'changes' ? Carbon::now()->addDays($i)->format('Y-m-d') : null;

                    Bell::create([
                        'type' => $type,
                        'variant' => $variant,
                        'week_day' => $week_day,
                    ]);
                }
            }
        }
    }
}
