<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class SetRest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    // 休憩機能 - 休憩入ボタンが正しく機能する
    public function testSetRestIn()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in_time' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        $response = $this->actingAs($user)->post('/attendance/restIn');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    // 休憩機能 - 休憩入は1日に何回もできる
    public function testSetRestInMultipleTimes()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in_time' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        $response = $this->actingAs($user)->post('/attendance/restIn');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        $response = $this->actingAs($user)->post('/attendance/restOut');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    // 休憩機能 - 休憩戻ボタンが表示されることを確認
    public function testSetRestOut()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in_time' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->post('/attendance/restIn');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        $response = $this->actingAs($user)->post('/attendance/restOut');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('勤務中');
    }

    // 休憩機能 - 休憩戻は1日に何回もできる
    public function testSetRestOutMultipleTimes()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in_time' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->post('/attendance/restIn');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->post('/attendance/restOut');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->post('/attendance/restIn');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

    // 休憩機能 - 休憩時刻が管理画面で管理できる
    public function testTotalRestTime()
    {
        $user = User::factory()->create([
                'email_verified_at' => now(),
            ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in_time' => Carbon::now()->subHour(),
        ]);

        $response = $this->actingAs($user)->post('/attendance/restIn');
        $response->assertStatus(302);

        $response = $this->actingAs($user)->post('/attendance/restOut');
        $response->assertStatus(302);

        $this->assertDatabaseHas('rests', [
            'rest_in_time' => Carbon::now(),
            'rest_out_time' => Carbon::now(),
        ]);

        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();

        $restRecord = $attendance->rests()->first();

        $restInTime = Carbon::parse($restRecord->rest_in_time);
        $restOutTime = Carbon::parse($restRecord->rest_out_time);

        $totalRestTime = $restOutTime->diffInMinutes($restInTime);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200)
            ->assertSee($totalRestTime);
    }
}