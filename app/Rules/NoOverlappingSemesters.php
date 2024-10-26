<?php

namespace App\Rules;

use App\Models\Semester;
use Carbon\Carbon; // Убедитесь, что путь к модели Semester верный
use Illuminate\Contracts\Validation\Rule;

class NoOverlappingSemesters implements Rule
{
    private $years;

    private $start;

    private $end;

    private $currentSemesterId;

    public function __construct($years, $start, $end, $currentSemesterId = null)
    {
        $this->years = $years;
        $this->start = Carbon::parse($start);
        $this->end = Carbon::parse($end);
        $this->currentSemesterId = $currentSemesterId;
    }

    public function passes($attribute, $value)
    {
        // Поиск пересекающихся семестров
        $overlappingSemester = Semester::where('years', $this->years)
            ->where(function ($query) {
                $query->whereBetween('start', [$this->start, $this->end])
                    ->orWhereBetween('end', [$this->start, $this->end])
                    ->orWhere(function ($query) {
                        $query->where('start', '<=', $this->start)
                            ->where('end', '>=', $this->end);
                    });
            })
            ->when($this->currentSemesterId, function ($query) {
                // Исключаем текущий семестр, если редактируем существующий
                $query->where('id', '!=', $this->currentSemesterId);
            })
            ->exists();

        // Возвращаем true, если пересечений нет
        return ! $overlappingSemester;
    }

    public function message()
    {
        return 'Указанный временной диапазон совпадает с существующим семестром.';
    }
}
