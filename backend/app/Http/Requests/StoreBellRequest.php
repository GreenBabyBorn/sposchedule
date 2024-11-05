<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBellRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'type' => 'required|in:main,changes', // Only 'main' or 'changes' are allowed
            'week_day' => 'nullable|string|size:2', // Nullable, should be a 2-character string if present
            'date' => 'nullable|date', // Nullable, must be a valid date if provided
            'building' => 'required|string|exists:buildings,name', // Must match an existing building name
            'is_preset' => 'boolean', // Should be true or false
            'name_preset' => 'nullable|string|unique:bells,name_preset', // Nullable but must be unique if provided
            'published' => 'boolean', // Should be true or false
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'Поле "тип" обязательно для заполнения.',
            'type.in' => 'Поле "тип" должно быть либо "main" либо "changes".',
            'week_day.size' => 'Поле "день недели" должно содержать ровно 2 символа.',
            'date.date' => 'Поле "дата" должно быть допустимой датой.',
            'building.required' => 'Поле "здание" обязательно для заполнения.',
            'building.exists' => 'Выбранное значение для поля "здание" неверно.',
            'name_preset.unique' => 'Такое значение "name_preset" уже существует.',
        ];
    }

}