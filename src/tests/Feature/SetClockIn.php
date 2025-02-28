<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class SetClockIn extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    // 出勤機能 - 出勤ボタンが正しく機能する
    public function testSetClockIn()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $today = now()->toDateString();
        $this->assertNull(Attendance::whereDate('date', $today)->where('user_id', $user->id)->first());

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        $response = $this->actingAs($user)->post('/attendance/clockIn');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('勤務中');
    }

    // 出勤機能 - 出勤は1日1回のみ可能
    public function testClockInOnceDay()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'date' => Carbon::today(),
            'user_id' => $user->id,
            'clock_in_time' => now()->subHours(4),
            'clock_out_time' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertDontSee('出勤');
    }

    // 出勤機能 - 出勤時刻が管理画面で確認できる
    public function testClockInView(){

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $today = now()->toDateString();
        $this->assertNull(Attendance::whereDate('date', $today)->where('user_id', $user->id)->first());

        $this->actingAs($user);

        $currentTime = Carbon::now()->format('H:i');

        $response = $this->actingAs($user)->post('/attendance/clockIn');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee($currentTime);
    }

}
