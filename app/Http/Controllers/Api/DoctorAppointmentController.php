<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\DoctorScheduleResource;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorAppointmentController extends Controller
{
    public function index()
    {
        $doctor = Auth::user()->doctor;

        $appointments = Appointment::with([
            'patient.user',
            'receptionist',
        ])
            ->where('doctor_id', $doctor->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Doctor appointments retrieved successfully',
            'data' => $appointments->items(),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ],
        ]);
    }


    public function updateStatus(Request $request, Appointment $appointment)
    {
        $doctor = Auth::user()->doctor;

        if ($appointment->doctor_id !== $doctor->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $appointment->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Appointment status updated successfully',
            'appointment' => $appointment,
        ]);
    }

    public function todayAppointments()
    {
        $doctor = Auth::user()->doctor;

        $appointments = Appointment::with([
            'patient.user',
            'doctor.user',
            'receptionist',
        ])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', Carbon::today())
            ->latest()
            ->paginate(10);

        return ApiResponse::success(
            'Today appointments retrieved successfully',
            [
                'data' => AppointmentResource::collection($appointments->items()),
                'meta' => [
                    'current_page' => $appointments->currentPage(),
                    'last_page' => $appointments->lastPage(),
                    'per_page' => $appointments->perPage(),
                    'total' => $appointments->total(),
                ],
            ]
        );
    }


    public function upcomingAppointments()
    {
        $doctor = Auth::user()->doctor;

        $appointments = Appointment::with([
            'patient.user',
            'doctor.user',
            'receptionist',
        ])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', '>=', Carbon::today())
            ->latest()
            ->paginate(10);

        return ApiResponse::success(
            'Upcoming appointments retrieved successfully',
            [
                'data' => AppointmentResource::collection($appointments->items()),
                'meta' => [
                    'current_page' => $appointments->currentPage(),
                    'last_page' => $appointments->lastPage(),
                    'per_page' => $appointments->perPage(),
                    'total' => $appointments->total(),
                ],
            ]
        );
    }

    public function schedules()
    {
        $doctor = Auth::user()->doctor;

        $schedules = DoctorSchedule::with('doctor.user')
            ->where('doctor_id', $doctor->id)
            ->latest()
            ->paginate(10);

        return ApiResponse::success(
            'Doctor schedules retrieved successfully',
            [
                'data' => DoctorScheduleResource::collection($schedules->items()),
                'meta' => [
                    'current_page' => $schedules->currentPage(),
                    'last_page' => $schedules->lastPage(),
                    'per_page' => $schedules->perPage(),
                    'total' => $schedules->total(),
                ],
            ]
        );
    }
}
