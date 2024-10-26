<?php

namespace App\Http\Requests\Schedule;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'group_id' => 'exists:groups,id',
            'date' => [
                'nullable',
                'date',
                'required_if:type,changes',
                Rule::unique('schedules')->where(function ($query) {
                    return $query->where(['group_id' => $this->safe()->input('group_id'), 'type' => $this->safe()->input('type')]);
                }),
            ],
            'type' => [

                Rule::in(['main', 'changes']),
            ],
            'week_type' => ['required_if:type,main', Rule::in(['ЧИСЛ', 'ЗНАМ'])],
            'week_day' => [
                'required_if:type,main',
                Rule::in(['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС']),
            ],

        ];
    }

    public function withValidator($validator)
    {

        $validator->after(function ($validator) {
            $date = $this->safe()->input('date');
            $week_type = $this->safe()->input('week_type');
            if (! $week_type) {
                return;
            }

            // День отсчета
            $currentDate = Carbon::parse('2024-09-01');

            $daysSinceStart = $currentDate->diffInDays($date);
            $weekNumber = intdiv($daysSinceStart, 7) + 1;
            $isOddWeek = $weekNumber % 2 !== 0;

            if (! $isOddWeek && $week_type === 'ЗНАМ') {
                return;
            }

            if ($isOddWeek && $week_type === 'ЧИСЛ') {
                return;
            }

            $validator->errors()->add('week_type', 'Неверный тип недели для указанной даты.');
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'group_id.required' => 'Поле "ID группы" обязательно для заполнения.',
            'group_id.exists' => 'Группа с указанным ID не найдена.',
            'date.required' => 'Поле "Дата" обязательно для заполнения.',
            'date.date' => 'Поле "Дата" должно быть корректной датой.',
            'date.unique' => 'Расписание на этот день для данной группы уже существует.',
            'date.required_if' => 'Поле "Дата" обязательно, когда тип расписания равен "changes".',
            'type.required' => 'Поле "Тип" обязательно для заполнения.',
            'type.in' => 'Поле "Тип" должно содержать одно из значений: main, changes.',
            'week_type.required' => 'Поле "Тип недели" обязательно для заполнения.',
            'week_type.in' => 'Поле "Тип недели" должно содержать одно из значений: ЗНАМ, ЧИСЛ.',
            'week_type.required_if' => 'Поле "Тип недели" обязательно, когда тип расписания равен "main".',
            'week_day.required' => 'Поле "День недели" обязательно для заполнения.',
            'week_day.in' => 'Поле "День недели" должно содержать одно из значений: ПН, ВТ, СР, ЧТ, ПТ, СБ, ВС.',
            'week_day.required_if' => 'Поле "День недели" обязательно, когда тип расписания равен "main".',
            'message.string' => 'Поле "Сообщение" должно быть строкой.',
            'message.required_if' => 'Поле "Сообщение" обязательно, когда режим отображения равен "message".',
        ];
    }
}
