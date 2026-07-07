<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DoctorAppointmentController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\DoctorScheduleController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PatientProfileController;
use App\Mail\TestMail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware([
    'auth:sanctum',
    'role:receptionist'
])->group(function () {

    Route::post('/doctors', [DoctorController::class, 'store']);

    Route::get('/doctors', [DoctorController::class, 'index']);

    Route::get('/doctors/{doctor}', [DoctorController::class, 'show']);

    Route::put('/doctors/{doctor}', [DoctorController::class, 'update']);

    Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy']);

    Route::get('/patients', [PatientController::class, 'index']);

    Route::get('/patients/{patient}', [PatientController::class, 'show']);

    Route::put('/patients/{patient}', [PatientController::class, 'update']);

    Route::delete('/patients/{patient}', [PatientController::class, 'destroy']);

    Route::post('/appointments', [AppointmentController::class, 'store']);

    Route::get('/appointments', [AppointmentController::class, 'index']);

    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);

    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);

    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::post('/doctor-schedules', [DoctorScheduleController::class, 'store']);

    Route::get('/doctor-schedules', [DoctorScheduleController::class, 'index']);

    Route::get('/doctor-schedules/{doctorSchedule}', [DoctorScheduleController::class, 'show']);

    Route::put('/doctor-schedules/{doctorSchedule}', [DoctorScheduleController::class, 'update']);

    Route::delete('/doctor-schedules/{doctorSchedule}', [DoctorScheduleController::class, 'destroy']);
});


Route::middleware([
    'auth:sanctum',
    'role:doctor'
])->group(function () {

    Route::get('/doctor/appointments', [DoctorAppointmentController::class, 'index']);
    Route::put(
        '/doctor/appointments/{appointment}/status',
        [DoctorAppointmentController::class, 'updateStatus']
    );
    Route::get(
        '/doctor/today-appointments',
        [DoctorAppointmentController::class, 'todayAppointments']
    );
    Route::get(
        '/doctor/upcoming-appointments',
        [DoctorAppointmentController::class, 'upcomingAppointments']
    );
    Route::get(
        '/doctor/schedules',
        [DoctorAppointmentController::class, 'schedules']
    );
});




Route::middleware([
    'auth:sanctum',
    'role:patient'
])->group(function () {

    Route::get('/patient/profile', [PatientProfileController::class, 'show']);
    Route::put('/patient/profile', [PatientProfileController::class, 'update']);
    Route::get('/patient/appointments', [PatientProfileController::class, 'appointments']);
    Route::post(
        '/patient/appointments',
        [PatientProfileController::class, 'bookAppointment']
    );
    Route::put(
        '/patient/appointments/{appointment}/cancel',
        [PatientProfileController::class, 'cancelAppointment']
    );
});





use App\Models\User;


Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);


    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json([
            'message' => 'Invalid verification link',
        ], 403);
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return response()->json([
        'message' => 'Email verified successfully',
    ]);
})->middleware(['signed'])->name('verification.verify');




Route::post(
    '/forgot-password',
    [PasswordResetController::class, 'forgotPassword']
);

Route::post(
    '/reset-password',
    [PasswordResetController::class, 'resetPassword']
);

Route::get('/reset-password/{token}', function ($token) {
    return response()->json([
        'token' => $token,
        'message' => 'Use this token with POST /api/reset-password',
    ]);
})->name('password.reset');
