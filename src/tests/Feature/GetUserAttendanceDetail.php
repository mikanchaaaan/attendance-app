<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class GetUserAttendanceDetail extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    // 勤怠詳細情報取得機能(一般ユーザ) - 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function testDetailDisplayName()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $dates = [];
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        $attendances = Attendance::factory(count($dates))
            ->withRest()
            ->create([
                'user_id' => $user->id,
                'date' => function () use (&$dates) {
                    return array_shift($dates);
                }
            ]);

        $this->actingAs($user);

        $attendance = $attendances->first();
        $response = $this->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    // 勤怠詳細情報取得機能(一般ユーザ) - 勤怠詳細画面の「日付」が選択した日付になっている
    public function testDetailDisplayDate()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $selectedDate = Carbon::now()->subDays(3);

        $attendance = Attendance::factory()->withRest()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response->assertSee($selectedDate->year . '年')
            ->assertSee($selectedDate->month . '月')
            ->assertSee($selectedDate->day . '日');
    }

    // 勤怠詳細情報取得機能(一般ユーザ) - 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function testDetailDisplayClock()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $clockInTime = Carbon::now()->startOfDay()->addHours(9);
        $clockOutTime = Carbon::now()->startOfDay()->addHours(18);

        $attendance = Attendance::factory()->withRest()->create([
            'user_id' => $user->id,
            'clock_in_time' => $clockInTime,
            'clock_out_time' => $clockOutTime,
            'date' => $clockInTime->format('Y-m-d'),
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response->assertSee($clockInTime->format('H:i'))
            ->assertSee($clockOutTime->format('H:i'));
        }

    // 勤怠詳細情報取得機能(一般ユーザ) - 「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function testDetailDisplayRest()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $clockInTime = Carbon::now()->startOfDay()->addHours(9);
        $clockOutTime = Carbon::now()->startOfDay()->addHours(18);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_time' => $clockInTime,
            'clock_out_time' => $clockOutTime,
            'date' => $clockInTime->format('Y-m-d'),
        ]);

        $restInTime = Carbon::now()->startOfDay()->addHours(12);
        $restOutTime = Carbon::now()->startOfDay()->addHours(13);

        $rest = Rest::create([
            'rest_in_time' => $restInTime,
            'rest_out_time' => $restOutTime,
        ]);

        $attendance->rests()->attach($rest->id);

        $this->actingAs($user);

        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response->assertSee($restInTime->format('H:i'))
            ->assertSee($restOutTime->format('H:i'));
    }
}
