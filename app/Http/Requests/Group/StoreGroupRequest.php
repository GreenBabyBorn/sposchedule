<?php

namespace App\Http\Requests\Group;

use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
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
            'course' => 'required|integer',
            'index' => 'required|string|max:255',
            'specialization' => 'required|string|max:255',
            'buildings' => 'required|array',
            'buildings.*.name' => 'required|exists:buildings,name|string',
            'semesters' => 'required|array',
            'semesters.*.id' => 'required|integer|exists:semesters,id',
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
            'course.required' => 'Поле "курс" обязательно для заполнения.',
            'course.integer' => 'Поле "курс" должно быть числом.',
            'index.required' => 'Поле "номер пары" обязательно для заполнения.',
            'index.string' => 'Поле "номер пары" должно быть строкой.',
            'index.max' => 'Поле "номер пары" не должно превышать 255 символов.',
            'specialization.required' => 'Поле "специальность" обязательно для заполнения.',
            'specialization.string' => 'Поле "специальность" должно быть строкой.',
            'specialization.max' => 'Поле "специальность" не должно превышать 255 символов.',
            'semesters.required' => 'Необходимо указать хотя бы один семестр.',
            'semesters.*.id.required' => 'ID семестра обязателен для заполнения.',
            'semesters.*.id.exists' => 'Указанный семестр не существует.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    // protected function prepareForValidation()
    // {
    //     $this->merge([
    //         'name' => $this->specialization . "-" . $this->course . $this->index,
    //     ]);
    // }

    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->after(function ($validator) {
            $name = $this->specialization . "-" . $this->course . $this->index;
            if (Group::where('name', $name)->exists()) {
                $validator->errors()->add('name', 'Группа с таким именем уже существует.');
            }
        });

        return $validator;
    }
}
