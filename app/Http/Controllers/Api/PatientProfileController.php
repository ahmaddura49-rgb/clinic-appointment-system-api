<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientProfileController extends Controller
{
    public function show()
    {
        $patient = Auth::user()->patient;

        return response()->json([
            'message' => 'Patient profile retrieved successfully',
            'patient' => $patient->load('user'),
        ]);
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string'],
        ]);

        $patient = Auth::user()->patient;

        $patient->update($validatedData);

        return response()->json([
            'message' => 'Patient profile updated successfully',
            'patient' => $patient->load('user'),
        ]);
    }
    public function appointments()
    {
        $patient = Auth::user()->patient;

        $appointments = Appointment::with([
            'doctor.user',
            'receptionist',
        ])
            ->where('patient_id', $patient->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Patient appointments retrieved successfully',
            'data' => $appointments->items(),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ],
        ]);
    }


    public function bookAppointment(BookAppointmentRequest $request)
    {
        $validatedData = $request->validated();

        $patient = Auth::user()->patient;

        $dayOfWeek = strtolower(date(
            'l',
            strtotime($validatedData['appointment_date'])
        ));

        $scheduleExists = DoctorSchedule::where('doctor_id', $validatedData['doctor_id'])
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $validatedData['appointment_time'])
            ->where('end_time', '>=', $validatedData['appointment_time'])
            ->exists();

        if (! $scheduleExists) {
            return ApiResponse::error(
                'Doctor is not available at this time',
                409
            );
        }

        $appointmentExists = Appointment::where('doctor_id', $validatedData['doctor_id'])
            ->where('appointment_date', $validatedData['appointment_date'])
            ->where('appointment_time', $validatedData['appointment_time'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($appointmentExists) {
            return ApiResponse::error(
                'This doctor already has an appointment at this time',
                409
            );
        }

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $validatedData['doctor_id'],
            'appointment_date' => $validatedData['appointment_date'],
            'appointment_time' => $validatedData['appointment_time'],
            'status' => 'pending',
            'notes' => $validatedData['notes'] ?? null,
        ]);

        return ApiResponse::success(
            'Appointment booked successfully',
            new AppointmentResource(
                $appointment->load([
                    'patient.user',
                    'doctor.user',
                ])
            ),
            201
        );
    }

    public function cancelAppointment(Appointment $appointment)
    {
        $patient = Auth::user()->patient;

        if ($appointment->patient_id !== $patient->id) {
            return ApiResponse::error(
                'Unauthorized',
                403
            );
        }

        $appointment->update([
            'status' => 'cancelled',
        ]);

        return ApiResponse::success(
            'Appointment cancelled successfully',
            new AppointmentResource(
                $appointment->load([
                    'patient.user',
                    'doctor.user',
                    'receptionist',
                ])
            )
        );
    }
}
