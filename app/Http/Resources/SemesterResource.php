<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SemesterResource extends JsonResource
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
            'years' => $this->years,
            'index' => $this->index,
            'name' => $this->years . " " . $this->index . " " . "семестр",
            'start' => $this->start,
            'end' => $this->end,

        ];
    }
}
