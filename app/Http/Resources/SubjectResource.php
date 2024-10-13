<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Получение query параметра, например "with_teachers", если он присутствует
        $includeTeachers = $request->query('teachers', false);

        // Базовый массив, который всегда возвращается
        return  [
            'id' => $this->id,
            'name' => $this->name,
            'teachers' => $this->when(
                $includeTeachers,
                TeacherResource::collection($this->teachers)
            ),
            'updated_at' => $this->updated_at,
        ];

    }
}
