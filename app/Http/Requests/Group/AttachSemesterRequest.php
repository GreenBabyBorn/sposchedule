<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttachSemesterRequest extends FormRequest
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
            // "semester_id" => 'required|integer|exists:subjects,id|unique:subject_teacher'
            "semester_id" => [
                'required',
                'integer',
                'exists:semesters,id',
            ]
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $semesterId = $this->input('semester_id');
            $groupId = $this->route('group')->id;

            if (DB::table('group_semester')->where('semester_id', $semesterId)->where('group_id', $groupId)->exists()) {
                $validator->errors()->add('semester_id', 'Этот семестр уже прикреплен к этому преподавателю.');
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
            'semester_id.required' => 'Поле "ID семестра" обязательно для заполнения.',
            'semester_id.integer' => 'Поле "ID семестра" должно быть числом.',
            'semester_id.exists' => 'Указанный семестр не существует.',
            'semester_id.unique' => 'Этот предмент уже добавлен к этому преподавателю.',
        ];
    }
}