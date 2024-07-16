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
            'name' => $this->specialization . "-" . $this->course . $this->index,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}