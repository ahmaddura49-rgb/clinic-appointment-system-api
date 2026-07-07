<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Appointment;

class Patient extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'date_of_birth',
        'gender',
        'address',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
