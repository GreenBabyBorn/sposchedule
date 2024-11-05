<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkinnyTeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 'id' => $this->id,
            // 'first_name' => $this->first_name,
            // 'last_name' => $this->last_name,
            // 'patronymic' => $this->patronymic,
            // 'name' => $this->last_name . " " . $this->first_name . " " . $this->patronymic,
            'name' => $this->name,
        ];
    }
}
