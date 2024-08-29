<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
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
            'date' => $this->date,
            'type' => $this->type,
            // 'week_type' => $this->week_type,
            'week_day' => $this->week_day,
            'view_mode' => $this->view_mode,
            'semester' => new SemesterResource($this->semester),
            'message' => $this->when($this->message !== null, $this->message),
            'group' => new GroupResource($this->group),
            'lessons' => LessonResource::collection($this->lessons),
            'published' => $this->published,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
