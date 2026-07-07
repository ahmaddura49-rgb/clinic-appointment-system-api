<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Appointment;

class Doctor extends Model
{
    protected $fillable = [
        'user_id',
        'specialization',
        'phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }
}
