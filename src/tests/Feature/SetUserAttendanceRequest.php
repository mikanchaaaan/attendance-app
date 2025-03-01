<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class SetUserAttendanceRequest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    // 勤怠詳細情報修正機能(一般ユーザ) - 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testClockValidation()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $selectedDate = Carbon::now()->subDays(3); // 任意の日付

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $requestedClockInTime = '18:00';
        $requestedClockOutTime = '14:00';

        $formData = [
            'date' => $selectedDate->format('Y-m-d'),
            'clock_in_time' => $requestedClockInTime,
            'clock_out_time' => $requestedClockOutTime,
            'comment' => 'テストコメント',
        ];

        $response = $this->post('/attendance/request', $formData);
        $response->assertRedirect("/attendance/{$attendance->id}");

        $response->assertSessionHasErrors(['clock_in_time', 'clock_out_time']);
        $clockInError = session('errors')->get('clock_in_time');
        $clockOutError = session('errors')->get('clock_out_time');
        $this->assertContains('出勤時間もしくは退勤時間が不適切な値です', $clockInError);
        $this->assertContains('出勤時間もしくは退勤時間が不適切な値です', $clockOutError);
    }

    // 勤怠詳細情報修正機能(一般ユーザ) - 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testRestInValidation()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $selectedDate = Carbon::now()->subDays(3);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $invalidRestInTime = '19:00';

        $formData = [
            'date' => $selectedDate->format('Y-m-d'),
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
            'rests' => [
                [
                    'rest_in_time' => $invalidRestInTime,
                ]
            ],
            'comment' => 'テストコメント',
        ];

        $response = $this->post('/attendance/request', $formData);
        $response->assertRedirect("/attendance/{$attendance->id}");

        $response->assertSessionHasErrors('rests.0.rest_in_time');

        $errors = session('errors')->getBag('default')->getMessages();

        $this->assertContains('休憩時間が勤務時間外です', $errors['rests.0.rest_in_time']);
    }

    // 勤怠詳細情報修正機能(一般ユーザ) - 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testRestOutValidation()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $selectedDate = Carbon::now()->subDays(3);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $invalidRestOutTime = '20:00';

        $formData = [
            'date' => $selectedDate->format('Y-m-d'),
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
            'rests' => [
                [
                    'rest_out_time' => $invalidRestOutTime,
                ]
            ],
            'comment' => 'テストコメント',
        ];

        $response = $this->post('/attendance/request', $formData);
        $response->assertRedirect("/attendance/{$attendance->id}");

        $response->assertSessionHasErrors('rests.0.rest_out_time');
        $errors = session('errors')->getBag('default')->getMessages();

        $this->assertContains('休憩時間が勤務時間外です', $errors['rests.0.rest_out_time']);
    }

    // 勤怠詳細情報修正機能(一般ユーザ) - 備考欄が未入力の場合のエラーメッセージが表示される
    public function testCommentValidation()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $selectedDate = Carbon::now()->subDays(3);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $formData = [
            'date' => $selectedDate->format('Y-m-d'),
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
            'comment' => Null,
        ];

        $response = $this->post('/attendance/request', $formData);
        $response->assertRedirect("/attendance/{$attendance->id}");

        $response->assertSessionHasErrors('comment');
        $errors = session('errors')->getBag('default')->getMessages();

        $this->assertContains('備考を記入してください', $errors['comment']);
    }

    // 勤怠詳細情報修正機能(一般ユーザ) - 修正申請処理が実行される
    public function testUserAttendanceRequest()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $selectedDate = Carbon::now()->subDays(3);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $modifiedClockInTime = '08:30';
        $modifiedClockOutTime = '17:30';

        $clockYear = $selectedDate->format('Y') . '年';
        $clockMonthDay = $selectedDate->format('m月d日');

        $formData = [
            'date' => $selectedDate->format('Y-m-d'),
            'clock_year' => $clockYear,
            'clock_monthDay' => $clockMonthDay,
            'clock_in_time' => $modifiedClockInTime,
            'clock_out_time' => $modifiedClockOutTime,
            'comment' => '修正申請テスト',
        ];

        $response = $this->post('/attendance/request', $formData);
        $response->assertRedirect('/stamp_correction_request/list');

        $modifiedClockInTimeWithSeconds = $modifiedClockInTime . ':00';
        $modifiedClockOutTimeWithSeconds = $modifiedClockOutTime . ':00';

        $this->assertDatabaseHas('attendance_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in_time' => $modifiedClockInTimeWithSeconds,
            'requested_clock_out_time' => $modifiedClockOutTimeWithSeconds,
            'status' => 'pending',
        ]);

        $adminUser = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee('pending');
    }

    // 勤怠詳細情報修正機能(一般ユーザ) - 「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function testUserViewPendingRequest()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $selectedDate = Carbon::now()->subDays(3);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $modifiedClockInTime = '08:30';
        $modifiedClockOutTime = '17:30';

        $clockYear = $selectedDate->format('Y') . '年';
        $clockMonthDay = $selectedDate->format('m月d日');

        $formData = [
            'date' => $selectedDate->format('Y-m-d'),
            'clock_year' => $clockYear,
            'clock_monthDay' => $clockMonthDay,
            'clock_in_time' => $modifiedClockInTime,
            'clock_out_time' => $modifiedClockOutTime,
            'comment' => '修正申請テスト',
        ];

        $response = $this->post('/attendance/request', $formData);
        $response->assertRedirect('/stamp_correction_request/list');

        $response = $this->get('/stamp_correction_request/list');

        $response->assertSee('修正申請テスト');
        $response->assertSee($selectedDate->format('Y/m/d'));
    }

    // 勤怠詳細情報修正機能(一般ユーザ) - 「承認済み」に管理者が承認した修正申請が全て表示されている
    public function testUserViewApprovedRequest()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $selectedDate = Carbon::now()->subDays(3);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $modifiedClockInTime = '08:30';
        $modifiedClockOutTime = '17:30';

        $clockYear = $selectedDate->format('Y') . '年';
        $clockMonthDay = $selectedDate->format('m月d日');

        $formData = [
            'date' => $selectedDate->format('Y-m-d'),
            'clock_year' => $clockYear,
            'clock_monthDay' => $clockMonthDay,
            'clock_in_time' => $modifiedClockInTime,
            'clock_out_time' => $modifiedClockOutTime,
            'comment' => '修正申請テスト',
        ];

        $response = $this->post('/attendance/request', $formData);
        $response->assertRedirect('/stamp_correction_request/list');

        $adminUser = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $this->actingAs($adminUser, 'admin');

        $attendanceRequest = AttendanceRequest::where('user_id', $user->id)->first();
        $attendanceRequest->status = 'approved';
        $attendanceRequest->save();

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);

        $response->assertSee('修正申請テスト');
        $response->assertSee($selectedDate->format('Y/m/d'));
        $response->assertSee('承認済み');
    }

    // 勤怠詳細情報修正機能(一般ユーザ) - 各申請の「詳細」を押下すると申請詳細画面に遷移する
    public function testMoveDetail()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $selectedDate = Carbon::now()->subDays(3);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
        ]);

        $this->actingAs($user);

        $modifiedClockInTime = '08:30';
        $modifiedClockOutTime = '17:30';

        $clockYear = $selectedDate->format('Y') . '年';
        $clockMonthDay = $selectedDate->format('m月d日');

        $formData = [
            'date' => $selectedDate->format('Y-m-d'),
            'clock_year' => $clockYear,
            'clock_monthDay' => $clockMonthDay,
            'clock_in_time' => $modifiedClockInTime,
            'clock_out_time' => $modifiedClockOutTime,
            'comment' => '修正申請テスト',
        ];

        $response = $this->post('/attendance/request', $formData);

        $modifiedClockInTimeWithSeconds = $modifiedClockInTime . ':00';
        $modifiedClockOutTimeWithSeconds = $modifiedClockOutTime . ':00';

        $this->assertDatabaseHas('attendance_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in_time' => $modifiedClockInTimeWithSeconds,
            'requested_clock_out_time' => $modifiedClockOutTimeWithSeconds,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);

        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)->first();
        $response = $this->get("/attendance/{$attendanceRequest->attendance_id}");
        $response->assertStatus(200);
    }
}
