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

        // 勤務当日の Attendance データがないことを確認
        $today = now()->toDateString();
        $this->assertNull(Attendance::whereDate('date', $today)->where('user_id', $user->id)->first());

        $response = $this->actingAs($user)->get('/attendance'); // ログイン状態でアクセス
        $response->assertStatus(200);

        // 画面に「勤務外」のステータスが表示されていることを確認
        $response->assertSee('勤務外');
    }

    // ステータス確認機能 - 出勤中の場合
    public function testStatusClockIn()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 今日の日付
        $today = now()->toDateString();

        // 出勤データを作成（このユーザーは出勤中）
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $today, // 勤務当日
            'clock_in_time' => now()->subHours(2), // 2時間前に出勤
            'clock_out_time' => null
        ]);

        // ユーザーとしてログインし、勤怠打刻画面を開く
        $response = $this->actingAs($user)->get('/attendance');

        // 正常にページが表示されることを確認
        $response->assertStatus(200);

        // 画面に「出勤中」のステータスが表示されていることを確認
        $response->assertSee('勤務中');
    }

    // ステータス確認機能 - 休憩中の場合
    public function testStatusRest()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 今日の日付
        $today = now()->toDateString();

        // 出勤データを作成（出勤していて、休憩中の状態）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $today, // 勤務当日
            'clock_in_time' => now()->subHours(2), // 2時間前に出勤
            'clock_out_time' => null,
        ]);

        // 休憩データを作成（休憩中）
        $attendance->rests()->create([
            'rest_in_time' => now()->subMinutes(30), // 30分前に休憩開始
            'rest_out_time' => null, // まだ休憩を終えていない
        ]);

        // ユーザーとしてログインし、勤怠打刻画面を開く
        $response = $this->actingAs($user)->get('/attendance');

        // 正常にページが表示されることを確認
        $response->assertStatus(200);

        // 画面に「休憩中」のステータスが表示されていることを確認
        $response->assertSee('休憩中');
    }

    // ステータス確認機能 - 退勤済みの場合
    public function testStatusClockOut()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 今日の日付
        $today = now()->toDateString();

        // 出勤データを作成（出勤していて、退勤済みの状態）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $today, // 勤務当日
            'clock_in_time' => now()->subHours(4), // 4時間前に出勤
            'clock_out_time' => now()->subHours(1), // 1時間前に退勤
        ]);

        // ユーザーとしてログインし、勤怠打刻画面を開く
        $response = $this->actingAs($user)->get('/attendance');

        // 正常にページが表示されることを確認
        $response->assertStatus(200);

        // 画面に「退勤済み」のステータスが表示されていることを確認
        $response->assertSee('退勤済み');
    }
}
