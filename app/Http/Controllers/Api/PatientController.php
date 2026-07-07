<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //search featuere
    public function index()
    {
        $patients = Patient::with('user')
            ->when(request('search'), function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->where('name', 'like', '%' . request('search') . '%')
                        ->orWhere('email', 'like', '%' . request('search') . '%');
                });
            })
            ->latest()
            ->paginate(10);

        // return response()->json([
        //     'message' => 'Patients retrieved successfully',
        //     'data' => $patients->items(),
        //     'meta' => [
        //         'current_page' => $patients->currentPage(),
        //         'last_page' => $patients->lastPage(),
        //         'per_page' => $patients->perPage(),
        //         'total' => $patients->total(),
        //     ],
        // ]);


        return ApiResponse::success(
            'Patients retrieved successfully',
            [
                'data' => PatientResource::collection($patients->items()),
                'meta' => [
                    'current_page' => $patients->currentPage(),
                    'last_page' => $patients->lastPage(),
                    'per_page' => $patients->perPage(),
                    'total' => $patients->total(),
                ],
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        return ApiResponse::success(
            'Patient retrieved successfully',
            new PatientResource($patient->load('user'))
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $validatedData = $request->validated();

        $userData = [
            'name' => $validatedData['name'] ?? $patient->user->name,
            'email' => $validatedData['email'] ?? $patient->user->email,
        ];

        if (isset($validatedData['password'])) {
            $userData['password'] = Hash::make($validatedData['password']);
        }

        $patient->user->update($userData);

        $patientData = [
            'phone' => $validatedData['phone'] ?? $patient->phone,
            'date_of_birth' => $validatedData['date_of_birth'] ?? $patient->date_of_birth,
            'gender' => $validatedData['gender'] ?? $patient->gender,
            'address' => $validatedData['address'] ?? $patient->address,
        ];

        $patient->update($patientData);

        return ApiResponse::success(
            'Patient updated successfully',
            new PatientResource($patient->load('user'))
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $patient->user->delete();

        return response()->json([
            'message' => 'Patient deleted successfully',
        ]);
    }
}
