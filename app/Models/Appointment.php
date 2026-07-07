<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\User;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'receptionist_id',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }


    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function receptionist()
    {
        return $this->belongsTo(User::class, 'receptionist_id');
    }
}
