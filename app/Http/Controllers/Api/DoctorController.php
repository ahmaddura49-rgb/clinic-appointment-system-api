<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctors = Doctor::with('user')
            ->latest()
            ->paginate(10);

        return ApiResponse::success(
            'Doctors retrieved successfully',
            [
                'data' => DoctorResource::collection($doctors->items()),
                'meta' => [
                    'current_page' => $doctors->currentPage(),
                    'last_page' => $doctors->lastPage(),
                    'per_page' => $doctors->perPage(),
                    'total' => $doctors->total(),
                ],
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDoctorRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'specialization' => $validatedData['specialization'],
            'phone' => $validatedData['phone'] ?? null,
        ]);

        return ApiResponse::success(
            'Doctor created successfully',
            new DoctorResource($doctor->load('user')),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor)
    {
        return ApiResponse::success(
            'Doctor retrieved successfully',
            new DoctorResource($doctor->load('user'))
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        $validatedData = $request->validated();

        $userData = [
            'name' => $validatedData['name'] ?? $doctor->user->name,
            'email' => $validatedData['email'] ?? $doctor->user->email,
        ];

        if (isset($validatedData['password'])) {
            $userData['password'] = Hash::make($validatedData['password']);
        }

        $doctor->user->update($userData);

        $doctorData = [
            'specialization' => $validatedData['specialization'] ?? $doctor->specialization,
            'phone' => $validatedData['phone'] ?? $doctor->phone,
        ];

        $doctor->update($doctorData);

        return ApiResponse::success(
            'Doctor updated successfully',
            new DoctorResource($doctor->load('user'))
        );
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        $doctor->user->delete();

        return response()->json([
            'message' => 'Doctor deleted successfully',
        ]);
    }
}

//Route Model Binding

//new Resource لعنصر واحد

//لقائمة عناصر Resource::collection()
