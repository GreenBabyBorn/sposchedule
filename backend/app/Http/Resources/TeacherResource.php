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
        $includeSubjects = $request->query('subjects', false);

        $data = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        if ($includeSubjects) {
            $data['subjects'] = SubjectResource::collection($this->subjects);
        }

        return $data;
    }
}