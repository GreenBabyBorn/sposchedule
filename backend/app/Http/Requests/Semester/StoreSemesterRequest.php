<?php

namespace App\Http\Requests\Semester;

use App\Rules\NoOverlappingSemesters;
use Illuminate\Foundation\Http\FormRequest;

class StoreSemesterRequest extends FormRequest
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
        $semesterId = $this->route('semester') ? $this->route('semester')->id : null; // Используйте при редактировании семестра

        return [
            'years' => 'required|',
            'index' => 'required|integer|min:0',
            'start' => ['required', 'date', 'before:end', new NoOverlappingSemesters($this->input('years'), $this->input('start'), $this->input('end'), $semesterId)],
            'end' => 'required|date|after:start',
        ];
    }
}
