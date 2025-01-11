<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoadRequest extends FormRequest
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
    public function rules()
    {
        return [
            'semester_id' => 'required|exists:semesters,id',
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'group_id'   => 'required|exists:groups,id',
            'hours'      => 'required|integer|min:1',


        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->recordExists()) {
                $validator->errors()->add('group_id', 'Такая нагрузка уже существует.');
            }
        });
    }

    protected function recordExists(): bool
    {
        return \App\Models\Load::where([
            'semester_id' => $this->semester_id,
            'teacher_id'  => $this->teacher_id,
            'subject_id'  => $this->subject_id,
            'group_id'    => $this->group_id,
        ])->exists();
    }


}
