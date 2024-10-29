<?php

namespace App\Http\Requests\Lesson;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends FormRequest
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
        $lessonId = $this->route('lesson') ? $this->route('lesson')->id : null;
        return [
            'subject_id' => 'nullable|exists:subjects,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'cabinet' => 'nullable|string|max:255',
            'index' => [
                'sometimes',
                'bail',
                'integer',
                'min:0',
                'max:10',
                // 'unique_schedule_index:{$lessonId}',

                Rule::unique('lessons')
                ->where('schedule_id', $this->input('schedule_id'))
                ->where('week_type', $this->input('week_type'))
                ->ignore($this->route('lesson')->id)
            ],
            'building' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'subject_id.required' => 'Поле "ID предмета" обязательно для заполнения.',
            'subject_id.exists' => 'Предмет с указанным ID не найден.',
            'schedule_id.required' => 'Поле "ID расписания" обязательно для заполнения.',
            'schedule_id.exists' => 'Расписание с указанным ID не найдено.',
            'cabinet.required' => 'Поле "Кабинет" обязательно для заполнения.',
            'cabinet.string' => 'Поле "Кабинет" должно быть строкой.',
            'cabinet.max' => 'Поле "Кабинет" не должно превышать 255 символов.',
            'index.required' => 'Поле "Номер пары" обязательно для заполнения.',
            'index.integer' => 'Поле "Номер пары" должно быть числом.',
            'index.min' => 'Поле "Номер пары" не может быть меньше 0.',
            'index.max' => 'Поле "Номер пары" не может быть больше 10.',
            'index.unique' => 'Номер пары уже существует для данного расписания.',
            'building.required' => 'Поле "Номер корпуса" обязательно для заполнения.',
            'building.integer' => 'Поле "Номер корпуса" должно быть числом.',
            'building.min' => 'Поле "Номер корпуса" не может быть меньше 1.',
        ];
    }
}