<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class GetUserAttendanceList extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    // 勤怠一覧情報取得機能（一般ユーザ） - 自分が行った勤怠情報が全て表示されている
    public function testViewAttendanceList()
    {
        Carbon::setLocale('ja');

        $user = User::factory()->create();

        $dates = [];
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        Attendance::factory(count($dates))
        ->withRest()
        ->create([
            'user_id' => $user->id,
            'date' => function () use (&$dates) {
                return array_shift($dates);
            }
        ]);

        $user = User::find($user->id);
        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        foreach ($user->attendances as $attendance) {
            $formattedDate = Carbon::parse($attendance->date)->isoFormat('MM/DD（dd）');
            $response->assertSee($formattedDate);
        }
    }

    // 勤怠一覧情報取得機能（一般ユーザ） - 勤怠一覧画面に遷移した際に現在の月が表示される
    public function testViewCurrentMonthList(){
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);
        $currentMonth = Carbon::now()->format('m');

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($currentMonth);
    }


    // 勤怠一覧情報取得機能（一般ユーザ） - 「前月」を押下した時に表示月の前月の情報が表示される
    public function testViewPreviousMonthList(){
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $previousMonth = Carbon::now()->subMonth();
        Attendance::factory(10)->withRest()->create([
            'user_id' => $user->id,
            'date' => $previousMonth->format('Y-m-d')
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->post('/attendance/list', ['month' => $previousMonth->format('Y-m')]);

        $response->assertSee($previousMonth->format('m'));

        foreach ($user->attendances as $attendance) {
            if ($attendance->date == $previousMonth->format('Y-m-d')) {
                $response->assertSee($attendance->date);
            }
        }
    }

    // 勤怠一覧情報取得機能（一般ユーザ） - 「翌月」を押下した時に表示月の前月の情報が表示される
    public function testViewNextMonthList(){
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $nextMonth = Carbon::now()->addMonth();
        Attendance::factory(10)->withRest()->create([
            'user_id' => $user->id,
            'date' => $nextMonth->format('Y-m-d')
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->post('/attendance/list', ['month' => $nextMonth->format('Y-m')]);

        $response->assertSee($nextMonth->format('m'));

        foreach ($user->attendances as $attendance) {
            if ($attendance->date == $nextMonth->format('Y-m-d')) {
                $response->assertSee($attendance->date);
            }
        }
    }

    // 勤怠一覧情報取得機能（一般ユーザ） - 勤怠詳細画面に遷移する
    public function testMoveDetail(){
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendanceDate = Carbon::now()->format('Y-m-d');
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $attendanceDate,
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response = $this->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);
    }
}
