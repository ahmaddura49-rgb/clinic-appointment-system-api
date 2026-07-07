<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPatients = Patient::count();

        $totalDoctors = Doctor::count();

        $totalAppointments = Appointment::count();

        $todayAppointments = Appointment::whereDate(
            'appointment_date',
            today()
        )->count();

        $confirmedAppointments = Appointment::where(
            'status',
            'confirmed'
        )->count();

        $pendingAppointments = Appointment::where(
            'status',
            'pending'
        )->count();

        $cancelledAppointments = Appointment::where(
            'status',
            'cancelled'
        )->count();

        return response()->json([
            'total_patients' => $totalPatients,
            'total_doctors' => $totalDoctors,
            'total_appointments' => $totalAppointments,
            'today_appointments' => $todayAppointments,

            'confirmed_appointments' => $confirmedAppointments,
            'pending_appointments' => $pendingAppointments,
            'cancelled_appointments' => $cancelledAppointments,
        ]);
    }
}
