<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'semester' => new SemesterResource($this->semester),
            'subject' => new SubjectResource($this->subject),
            'teacher' => new TeacherResource($this->teacher),
            'group' => new SkinnyGroup($this->group),
            'hours' => $this->hours
        ];
    }



}
