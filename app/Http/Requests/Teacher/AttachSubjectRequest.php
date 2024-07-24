<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            // "subject_id" => 'required|integer|exists:subjects,id|unique:subject_teacher'
            "subject_id" => [
                'required',
                'integer',
                'exists:subjects,id',
            ]
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $subjectId = $this->input('subject_id');
            $teacherId = $this->route('teacher')->id;

            if (DB::table('subject_teacher')->where('subject_id', $subjectId)->where('teacher_id', $teacherId)->exists()) {
                $validator->errors()->add('subject_id', 'Этот предмет уже прикреплен к этому преподавателю.');
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
            'subject_id.required' => 'Поле "ID предмета" обязательно для заполнения.',
            'subject_id.integer' => 'Поле "ID предмета" должно быть числом.',
            'subject_id.exists' => 'Указанный предмет не существует.',
            'subject_id.unique' => 'Этот предмент уже добавлен к этому преподавателю.',
        ];
    }
}
