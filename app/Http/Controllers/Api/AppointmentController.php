<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appointments = Appointment::with([
            'patient.user',
            'doctor.user',
            'receptionist'
        ])
            ->when(request('date'), function ($query) {
                $query->where('appointment_date', request('date'));
            })
            ->when(request('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->when(request('doctor_id'), function ($query) {
                $query->where('doctor_id', request('doctor_id'));
            })
            ->when(request('patient_id'), function ($query) {
                $query->where('patient_id', request('patient_id'));
            })
            ->latest()
            ->paginate(10);

        // return response()->json([
        //     'message' => 'Appointments retrieved successfully',
        //     'data' => $appointments->items(),
        //     'meta' => [
        //         'current_page' => $appointments->currentPage(),
        //         'last_page' => $appointments->lastPage(),
        //         'per_page' => $appointments->perPage(),
        //         'total' => $appointments->total(),
        //     ],
        // ]);
        return ApiResponse::success(
            'Appointments retrieved successfully',
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentRequest $request)
    {
        $validatedData = $request->validated();

        $validatedData['receptionist_id'] = Auth::id();

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
            return response()->json([
                'message' => 'Doctor is not available at this time',
            ], 409);
        }

        $appointmentExists = Appointment::where('doctor_id', $validatedData['doctor_id'])
            ->where('appointment_date', $validatedData['appointment_date'])
            ->where('appointment_time', $validatedData['appointment_time'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($appointmentExists) {
            return response()->json([
                'message' => 'This doctor already has an appointment at this time',
            ], 409);
        }

        $appointment = Appointment::create($validatedData);

        return ApiResponse::success(
            'Appointment created successfully',
            new AppointmentResource(
                $appointment->load([
                    'patient.user',
                    'doctor.user',
                    'receptionist',
                ])
            ),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        // return response()->json([
        //     'message' => 'Appointment retrieved successfully',
        //     'appointment' => $appointment->load([
        //         'patient.user',
        //         'doctor.user',
        //         'receptionist',
        //     ]),
        // ]);

        return ApiResponse::success(
            'Appointment retrieved successfully',
            new AppointmentResource(
                $appointment->load([
                    'patient.user',
                    'doctor.user',
                    'receptionist',
                ])
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $validatedData = $request->validated();

        $doctorId = $validatedData['doctor_id'] ?? $appointment->doctor_id;
        $appointmentDate = $validatedData['appointment_date'] ?? $appointment->appointment_date;
        $appointmentTime = $validatedData['appointment_time'] ?? $appointment->appointment_time;

        $appointmentExists = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $appointmentDate)
            ->where('appointment_time', $appointmentTime)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($appointmentExists) {
            return response()->json([
                'message' => 'This doctor already has an appointment at this time',
            ], 409);
        }

        $appointment->update($validatedData);

        return ApiResponse::success(
            'Appointment updated successfully',
            new AppointmentResource(
                $appointment->load([
                    'patient.user',
                    'doctor.user',
                    'receptionist',
                ])
            )
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return response()->json([
            'message' => 'Appointment deleted successfully',
        ]);
    }
}
