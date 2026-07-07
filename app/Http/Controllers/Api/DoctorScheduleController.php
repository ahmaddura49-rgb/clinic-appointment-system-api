<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorScheduleRequest;
use App\Http\Requests\UpdateDoctorScheduleRequest;
use App\Http\Resources\DoctorScheduleResource;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;

class DoctorScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = DoctorSchedule::with('doctor.user')
            ->latest()
            ->paginate(10);

        // return response()->json([
        //     'message' => 'Doctor schedules retrieved successfully',
        //     'data' => $schedules->items(),
        //     'meta' => [
        //         'current_page' => $schedules->currentPage(),
        //         'last_page' => $schedules->lastPage(),
        //         'per_page' => $schedules->perPage(),
        //         'total' => $schedules->total(),
        //     ],
        // ]);
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDoctorScheduleRequest $request)
    {
        $validatedData = $request->validated();

        $scheduleExists = DoctorSchedule::where('doctor_id', $validatedData['doctor_id'])
            ->where('day_of_week', $validatedData['day_of_week'])
            ->where('start_time', $validatedData['start_time'])
            ->where('end_time', $validatedData['end_time'])
            ->exists();

        if ($scheduleExists) {
            return response()->json([
                'message' => 'This schedule already exists for this doctor',
            ], 409);
        }

        $schedule = DoctorSchedule::create($validatedData);

        return ApiResponse::success(
            'Doctor schedule created successfully',
            new DoctorScheduleResource(
                $schedule->load('doctor.user')
            ),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(DoctorSchedule $doctorSchedule)
    {
        return ApiResponse::success(
            'Doctor schedule retrieved successfully',
            new DoctorScheduleResource(
                $doctorSchedule->load('doctor.user')
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorScheduleRequest $request, DoctorSchedule $doctorSchedule)
    {
        $validatedData = $request->validated();

        $doctorId = $validatedData['doctor_id'] ?? $doctorSchedule->doctor_id;
        $dayOfWeek = $validatedData['day_of_week'] ?? $doctorSchedule->day_of_week;
        $startTime = $validatedData['start_time'] ?? $doctorSchedule->start_time;
        $endTime = $validatedData['end_time'] ?? $doctorSchedule->end_time;

        $scheduleExists = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', $startTime)
            ->where('end_time', $endTime)
            ->where('id', '!=', $doctorSchedule->id)
            ->exists();

        if ($scheduleExists) {
            return response()->json([
                'message' => 'This schedule already exists for this doctor',
            ], 409);
        }

        $doctorSchedule->update($validatedData);

        return ApiResponse::success(
            'Doctor schedule updated successfully',
            new DoctorScheduleResource(
                $doctorSchedule->load('doctor.user')
            )
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DoctorSchedule $doctorSchedule)
    {
        $doctorSchedule->delete();

        // return response()->json([
        //     'message' => 'Doctor schedule deleted successfully',
        // ]);

        return ApiResponse::success(
            'Doctor schedule deleted successfully'
        );
    }
}
