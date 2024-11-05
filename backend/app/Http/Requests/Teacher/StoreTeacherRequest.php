<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Exceptions\TeacherExistsException;
use App\Models\Teacher;

class StoreTeacherRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:teachers,name',

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
            $name = $this->input('name');
            $existingSubject = Teacher::where('name', $name)->first();

            if ($existingSubject) {
                // Throw the custom exception if a duplicate subject is found
                throw new TeacherExistsException($existingSubject->id);
            }
        });
    }
}