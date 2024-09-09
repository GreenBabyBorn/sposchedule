<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class BellsPeriodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'bells_id' => $this->bells_id,
            'index' => $this->index,
            'has_break' => $this->has_break,
            'period_from' => $this->formatTimeWithoutSeconds($this->period_from),
            'period_to' => $this->formatTimeWithoutSeconds($this->period_to),
            'period_from_after' => $this->formatTimeWithoutSeconds($this->period_from_after),
            'period_to_after' => $this->formatTimeWithoutSeconds($this->period_to_after),
        ];
    }

    /**
     * Format time without seconds.
     *
     * @param  string|null  $value
     * @return string|null
     */
    private function formatTimeWithoutSeconds($value)
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }
}
