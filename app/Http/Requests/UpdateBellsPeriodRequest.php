<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBellsPeriodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'bells_id' => 'required|exists:bells,id', // Проверяем существование bells_id
            'index' => 'required|integer|min:0|unique:bells_periods,index,' . $this->route('bells_period') . ',id,bells_id,' . $this->bells_id, // Уникальность индекса с исключением текущего id
            'has_break' => 'boolean',
            'period_from' => 'required|date_format:H:i', // Время в формате HH:MM
            'period_to' => 'required|date_format:H:i|after:period_from', // Время окончания должно быть после времени начала
            'period_from_after' => 'nullable|date_format:H:i', // Дополнительное время начала
            'period_to_after' => 'nullable|date_format:H:i|after:period_from_after', // Дополнительное время окончания должно быть после дополнительного времени начала
        ];
    }

    public function messages()
    {
        return [
            'index.unique' => 'Звонок на эту пару уже существует для указанного расписания.',
            'period_to.after' => 'Время окончания должно быть позже времени начала.',
            'period_to_after.after' => 'Дополнительное время окончания должно быть позже дополнительного времени начала.',
        ];
    }
}
