<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class AttachSubjectRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "subject_id" => 'required|integer|exists:subjects,id|unique:subject_teacher'
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
            'subject_id.integer' => 'Поле "ID предмета" должно быть числом.',
            'subject_id.exists' => 'Указанный предмет не существует.',
            'subject_id.unique' => 'Этот предмент уже добавлен к этому преподавателю.',
        ];
    }
}
