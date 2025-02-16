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
}

