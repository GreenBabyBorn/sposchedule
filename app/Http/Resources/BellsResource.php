<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BellsResource extends JsonResource
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
            'type' => $this->type,
            'date' => $this->date,
            'is_preset' => $this->is_preset,
            'name_preset' => $this->name_preset,
            'building' => $this->building,
            'published' => $this->published,
            'periods' => BellsPeriodResource::collection($this->periods),
        ];
    }
}
