<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class DetachSubjectRequest extends FormRequest
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
            'subject_id' => 'required|integer|exists:subjects,id|integer',
        ];
    }

    /**
     * Custom validation logic
     */
    public function withValidator($validator)
    {

        $validator->after(function ($validator) {
            $teacher = $this->route('teacher');
            $subjectId = $this->safe()->input('subject_id');

            if (! $teacher->subjects()->where('subject_id', $subjectId)->exists()) {
                $validator->errors()->add('subject_id', 'Этот предмет не прикреплен к этому преподавателю.');
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
            'subject_id.exists' => 'Предмет с указанным ID не найден.',
            'subject_id.integer' => 'Поле "ID предмета" должно быть числом.',
        ];
    }
}
