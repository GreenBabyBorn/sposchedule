<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class DetachSemesterRequest extends FormRequest
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
            'semester_id' => 'required|integer|exists:semesters,id|integer',
        ];
    }

    /**
     * Custom validation logic
     */
    public function withValidator($validator)
    {

        $validator->after(function ($validator) {
            $group = $this->route('group');
            $semesterId = $this->safe()->input('semester_id');

            if (! $group->semesters()->where('semester_id', $semesterId)->exists()) {
                $validator->errors()->add('semester_id', 'Этот семестр не прикреплен к этой группе.');
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
            'semester_id.exists' => 'Предмет с указанным ID не найден.',
            'semester_id.integer' => 'Поле "ID семестра" должно быть числом.',
        ];
    }
}
