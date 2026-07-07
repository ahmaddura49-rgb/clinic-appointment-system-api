<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        $validatedData['password'] = Hash::make($validatedData['password']);

        $validatedData['role'] = 'patient';

        $user = User::create($validatedData);

        $patient = Patient::create([
            'user_id' => $user->id,
        ]);

        $user->sendEmailVerificationNotification();

        return ApiResponse::success(
            'Patient registered successfully. Please check your email to verify your account.',
            [
                'user' => $user,
                'patient' => $patient,
            ],
            201
        );
    }


    public function login(LoginRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::where('email', $validatedData['email'])->first();

        if (! $user || ! Hash::check($validatedData['password'], $user->password)) {

            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        //     if (! Auth::attempt($validatedData)) {
        // return response()->json([
        //     'message' => 'Invalid credentials'
        // ], 401);
        // }

        // $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email first'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }


    public function logout()
    {
        $user = Auth::user();

        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
