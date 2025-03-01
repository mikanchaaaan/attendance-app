<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class SetClockOut extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    // 退勤機能 - 退勤ボタンが正しく機能する
    public function testSetClockOut()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in_time' => Carbon::now(),
        ]);

        $this->assertNull($attendance->clock_out_time);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        $response = $this->actingAs($user)->post('/attendance/clockOut');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済み');
    }

    // 退勤機能 - 退勤時刻が管理画面で確認できる
    public function testClockInView()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $today = now()->toDateString();
        $this->assertNull(Attendance::whereDate('date', $today)->where('user_id', $user->id)->first());

        $currentTime = Carbon::now()->format('H:i');

        $response = $this->actingAs($user)->post('/attendance/clockIn');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->post('/attendance/clockOut');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee($currentTime);
    }
}
