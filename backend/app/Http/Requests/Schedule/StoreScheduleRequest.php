<?php

namespace App\Http\Requests\Schedule;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreScheduleRequest extends FormRequest
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
            'group_id' => ['required', 'exists:groups,id',
                function ($attribute, $value, $fail) {
                    if ($this->type === 'main') {
                        $existingSchedule = \App\Models\Schedule::where('group_id', $this->group_id)
                            ->where('type', $this->type)
                            ->where('week_day', $this->week_day)
                            ->where('semester_id', $this->semester_id)
                            ->first();

                        if ($existingSchedule) {
                            throw new \App\Exceptions\ScheduleExistsException($existingSchedule->id);
                        }
                    } elseif ($this->type === 'changes') {
                        $existingSchedule = \App\Models\Schedule::where('group_id', $this->group_id)
                            ->where('type', $this->type)
                            ->where('date', $this->date)
                            ->where('semester_id', $this->semester_id)
                            ->first();

                        if ($existingSchedule) {
                            throw new \App\Exceptions\ScheduleExistsException($existingSchedule->id);
                        }
                    }
                },
            ],
            'date' => [
                'nullable',
                'required_if:type,changes',
                // Проверяем уникальность по группе и типу расписания, с учетом преобразованной даты
                Rule::unique('schedules')->where(function ($query) {
                    $groupId = $this->input('group_id');
                    $type = $this->input('type');

                    // Учитываем только те записи, где group_id и type совпадают с текущими значениями
                    return $query->where('group_id', $groupId)
                        ->where('type', $type);
                }),
            ],
            'type' => [
                'required',
                Rule::in(['main', 'changes']),
            ],
            // 'week_type' => ['required_if:type,main', Rule::in(['ЧИСЛ', 'ЗНАМ'])],
            'week_day' => [
                'required_if:type,main',
                Rule::in(['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС']),
            ],

        ];
    }

    public function prepareForValidation()
    {
        // Преобразуем дату в формат 'Y-m-d' перед валидацией, если она существует и в формате 'dd.mm.yyyy'
        if ($this->has('date') && preg_match('/\d{2}\.\d{2}\.\d{4}/', $this->input('date'))) {
            try {
                $this->merge([
                    'date' => Carbon::createFromFormat('d.m.Y', $this->input('date'))->format('Y-m-d'),
                ]);
            } catch (\Exception $e) {
                throw new ValidationException('Дата в неправильном формате. Пожалуйста, используйте формат dd.mm.yyyy.');
            }
        }
    }

    public function withValidator($validator)
    {

        $validator->after(function ($validator) {
            // $date = $this->safe()->input('date');
            $week_type = $this->safe()->input('week_type');
            if (! $week_type) {
                return;
            }

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
