<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;
use App\Exceptions\SubjectExistsException;
use App\Models\Subject;
use Illuminate\Validation\Validator;

class StoreSubjectRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:subjects,name',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Поле является обязательным для заполнения.',
            'name.string' => 'Название предмета должно быть строкой.',
            'name.max' => 'Длина названия предмета не должна превышать 255 символов.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            $name = $this->input('name');
            $existingSubject = Subject::where('name', $name)->first();

            if ($existingSubject) {
                // Throw the custom exception if a duplicate subject is found
                throw new SubjectExistsException($existingSubject->id);
            }
        });
    }
}