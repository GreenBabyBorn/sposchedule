<?php

namespace App\Http\Requests\Lesson;

use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
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
        // Получаем значение поля message, если оно присутствует в запросе
        $hasMessage = $this->filled('message');
        $rules = [];
        // Определяем базовые правила
        if ($hasMessage) {
            $rules = [
                'week_type' => ['nullable', Rule::in(['ЧИСЛ', 'ЗНАМ'])],
                'index' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:10',
                    'unique_schedule_index'
                ],
            ];
        } else {
            $rules = [
                'subject_id' => 'required|exists:subjects,id',
                'schedule_id' => 'required|exists:schedules,id',
                'cabinet' => 'nullable|string|max:255',
                'week_type' => ['nullable', Rule::in(['ЧИСЛ', 'ЗНАМ'])],
                'index' => [
                    'bail',
                    'required',
                    'integer',
                    'min:0',
                    'max:10',
                    'unique_schedule_index'
                ],
                'building' => 'nullable|string|min:1',
            ];
        }

        return $rules;
    }

    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {

    //         // Извлекаем значения из запроса
    //         $scheduleId = $this->input('schedule_id');
    //         $index = $this->input('index');
    //         $weekType = $this->input('week_type');

    //         // Ищем совпадающие уроки с таким же schedule_id и index
    //         $existingLessons = Lesson::where('schedule_id', $scheduleId)
    //             ->where('index', $index)
    //             ->get();

    //         // Фильтрация уроков с несовпадающими типами недели
    //         $hasSameType = $existingLessons->contains(function ($lesson) use ($weekType) {
    //             return $lesson->week_type === $weekType;
    //         });

    //         // Если существует урок с таким же типом недели, добавляем ошибку
    //         if ($hasSameType) {
    //             $validator->errors()->add('index', 'Пара с таким же номером и расписанием уже существует.');
    //         }

    //         // Проверяем случаи "ЧИСЛ" и "ЗНАМ"
    //         if ($weekType === 'ЧИСЛ' || $weekType === 'ЗНАМ') {
    //             $oppositeType = $weekType === 'ЧИСЛ' ? 'ЗНАМ' : 'ЧИСЛ';
    //             $oppositeExists = $existingLessons->contains('week_type', $oppositeType);

    //             // Если существует противоположный тип, то разрешаем добавление
    //             if ($oppositeExists) {
    //                 return;
    //             }
    //         }

    //         // Если оба week_type не разрешают добавление, отклоняем запрос
    //         if ($existingLessons->isNotEmpty()) {
    //             $validator->errors()->add('index', 'Невозможно добавить урок: дублирование schedule_id и index.');
    //         }
    //     });
    // }

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
