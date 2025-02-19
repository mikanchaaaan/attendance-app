<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'rest_in_time',
        'rest_out_time',
    ];

    public function attendanceRests()
    {
        return $this->belongsToMany(Attendance::class, 'attendance_rest', 'rest_id', 'attendance_id');
    }

    public function attendanceRequests()
    {
        return $this->belongsToMany(AttendanceRequest::class, 'attendance_request_rest', 'rest_id', 'attendance_request_id');
    }

    public function getFormattedRestInTimeAttribute()
    {
        return $this->rest_in_time ? \Carbon\Carbon::parse($this->rest_in_time)->format('H:i') : '';
    }

    public function getFormattedRestOutTimeAttribute()
    {
        return $this->rest_out_time ? \Carbon\Carbon::parse($this->rest_out_time)->format('H:i') : '';
    }
}

