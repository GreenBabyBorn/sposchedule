<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $includeTeachers = $request->query('teachers', false);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'subjects' => $this->when(! $includeTeachers, SubjectResource::collection($this->subjects)),
        ];
    }
}
