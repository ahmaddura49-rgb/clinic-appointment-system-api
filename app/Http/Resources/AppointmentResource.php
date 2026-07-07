<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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

            'patient' => [
                'id' => $this->patient->id,
                'name' => $this->patient->user->name,
                'email' => $this->patient->user->email,
            ],

            'doctor' => [
                'id' => $this->doctor->id,
                'name' => $this->doctor->user->name,
                'specialization' => $this->doctor->specialization,
            ],

            'receptionist' => $this->receptionist ? [
                'id' => $this->receptionist->id,
                'name' => $this->receptionist->name,
            ] : null,

            'appointment_date' => $this->appointment_date,
            'appointment_time' => $this->appointment_time,
            'status' => $this->status,
            'notes' => $this->notes,

            'created_at' => $this->created_at,
        ];
    }
}
