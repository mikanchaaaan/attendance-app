<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\ResponseTrait;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class GetAttendanceStatus extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    // ステータス確認機能 - 勤務外の場合
    public function testStatusOffDuty()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $today = now()->toDateString();
        $this->assertNull(Attendance::whereDate('date', $today)->where('user_id', $user->id)->first());

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('勤務外');
    }

    // ステータス確認機能 - 出勤中の場合
    public function testStatusClockIn()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $today = now()->toDateString();

        // 出勤データを作成（このユーザーは出勤中）
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $today, // 勤務当日
            'clock_in_time' => now()->subHours(2),
            'clock_out_time' => null
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('勤務中');
    }

    // ステータス確認機能 - 休憩中の場合
    public function testStatusRest()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $today = now()->toDateString();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in_time' => now()->subHours(2),
            'clock_out_time' => null,
        ]);

        $attendance->rests()->create([
            'rest_in_time' => now()->subMinutes(30),
            'rest_out_time' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('休憩中');
    }

    // ステータス確認機能 - 退勤済みの場合
    public function testStatusClockOut()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $today = now()->toDateString();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in_time' => now()->subHours(4),
            'clock_out_time' => now()->subHours(1),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('退勤済み');
    }
}
