<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use App\Exceptions\TeacherExistsException;
use Illuminate\Validation\Validator;
use App\Models\Teacher;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
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
           'name' => [
            'required',
            'string',
            'max:255',
        ],
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
            'name.required' => 'Поле ФИО обязательно для заполнения.',
            'name.string' => 'Поле ФИО должно быть строкой.',
            'name.max' => 'Поле ФИО не должно превышать 255 символов.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            $teacher = $this->route('teacher');
            $name = $this->input('name');
            $existingTeacher = Teacher::where('name', $name)->first();
            if($existingTeacher->id === $teacher->id) {
                return;
            }
            if ($existingTeacher) {
                // Throw the custom exception if a duplicate subject is found
                throw new TeacherExistsException($existingTeacher->id);
            }
        });
    }
}