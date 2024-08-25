<?php

namespace App\Http\Resources;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
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
            'schedule_id' => $this->schedule_id,
            'index' => $this->index,
            'cabinet' => $this->cabinet,
            'subject' => new SubjectResource($this->subject),
            'teachers' => TeacherResource::collection($this->teachers),
            'building' => $this->building,
            'week_type' => $this->week_type,
        ];
    }
}
