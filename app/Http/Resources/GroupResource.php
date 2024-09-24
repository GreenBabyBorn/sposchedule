<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course' => $this->course,
            'index' => $this->index,
            'specialization' => $this->specialization,
            'name' => $this->name,
            'buildings' => $this->buildings,
            'semesters' => SemesterResource::collection($this->semesters),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
