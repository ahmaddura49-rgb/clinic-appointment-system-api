<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id' => $this->id,

            'doctor' => [
                'id' => $this->doctor->id,
                'name' => $this->doctor->user->name,
                'specialization' => $this->doctor->specialization,
            ],

            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,

            'created_at' => $this->created_at,
        ];
    }
}
