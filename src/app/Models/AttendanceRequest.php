<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_id',
        'status',
        'comment',
        'requested_clock_date',
        'requested_clock_in_time',
        'requested_clock_out_time'
    ];

    protected $casts = [
        'requested_clock_date' => 'date',
    ];

    public function rests()
    {
        return $this->belongsToMany(Rest::class, 'attendance_request_rest', 'attendance_request_id', 'rest_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
