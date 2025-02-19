<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in_time',
        'clock_out_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rests()
    {
        return $this->belongsToMany(Rest::class, 'attendance_rest', 'attendance_id', 'rest_id');
    }

    public function getFormattedClockInTimeAttribute()
    {
        return $this->clock_in_time ? Carbon::parse($this->clock_in_time)->format('H:i') : '';
    }

    public function getFormattedClockOutTimeAttribute()
    {
        return $this->clock_in_time ? Carbon::parse($this->clock_out_time)->format('H:i') : '';
    }
}