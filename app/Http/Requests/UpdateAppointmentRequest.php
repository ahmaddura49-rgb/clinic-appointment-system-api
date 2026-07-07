<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['sometimes', 'exists:patients,id'],
            'doctor_id' => ['sometimes', 'exists:doctors,id'],

            'appointment_date' => ['sometimes', 'date'],
            'appointment_time' => ['sometimes', 'date_format:H:i'],

            'status' => ['sometimes', 'in:pending,confirmed,cancelled,completed'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
