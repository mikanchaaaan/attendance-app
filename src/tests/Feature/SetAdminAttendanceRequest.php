<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class SetAdminAttendanceRequest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    // 勤怠情報修正機能（管理者) - 承認待ちの修正申請が全て表示されている
    public function testAdminViewAttendancePending()
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin_password'),
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $selectedDate = Carbon::now()->subDays(3);
        $attendance = Attendance::factory()->withRest()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
        ]);

        $attendanceRequest = AttendanceRequest::create([
            'user_id' => $user->id,
            'requested_clock_date' => $selectedDate,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'comment' => 'test',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        $response->assertSee($attendanceRequest->comment);
        $response->assertSee($user->name);
        $response->assertSee('承認待ち');
    }

    // 勤怠情報修正機能（管理者）- 承認済みの修正申請が全て表示されている
    public function testAdminViewAttendanceApproved()
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin_password'),
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $selectedDate = Carbon::now()->subDays(3);
        $attendance = Attendance::factory()->withRest()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
        ]);

        $attendanceRequest = AttendanceRequest::create([
            'user_id' => $user->id,
            'requested_clock_date' => $selectedDate,
            'attendance_id' => $attendance->id,
            'status' => 'approved',
            'comment' => 'test',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);

        $response->assertSee($attendanceRequest->comment);
        $response->assertSee($user->name);
        $response->assertSee('承認済み');
    }

    // 勤怠情報修正機能（管理者）- 修正申請の詳細内容が正しく表示されている
    public function testAdminViewAttendanceRequestDetail()
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin_password'),
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $selectedDate = Carbon::now()->subDays(3);
        $attendance = Attendance::factory()->withRest(2)->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
        ]);

        $attendanceRequest = AttendanceRequest::create([
            'user_id' => $user->id,
            'requested_clock_date' => $selectedDate,
            'requested_clock_in_time' => $attendance->clock_in_time,
            'requested_clock_out_time' => $attendance->clock_out_time,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'comment' => 'test',
        ]);

        $rest = Rest::create([
            'attendance_id' => $attendance->id,
            'rest_in_time' => '12:00:00',
            'rest_out_time' => '12:30:00',
        ]);

        $attendanceRequest->rests()->attach($rest->pluck('id'));

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)->first();
        $response = $this->actingAs($admin, 'admin')->get("/stamp_correction_request/approve/{$attendanceRequest->id}");
        $response->assertStatus(200);

        $response->assertSee($attendanceRequest->comment);
        $response->assertSee($user->name);
        $expectedYear = Carbon::parse($attendanceRequest->requested_clock_date)->format('Y') . '年';
        $expectedMonthDay = Carbon::parse($attendanceRequest->requested_clock_date)->format('n') . '月' . Carbon::parse($attendanceRequest->requested_clock_date)->format('j') . '日';

        $response->assertSee($expectedYear);
        $response->assertSee($expectedMonthDay);
    }

    // 勤怠情報修正機能（管理者）- 修正申請の承認処理が正しく行われる
    public function testAdminApproveAttendanceRequest()
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin_password'),
        ]);

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

        $attendanceRequest = AttendanceRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_date' => $attendance->date,
            'requested_clock_in_time' => '10:00:00',
            'requested_clock_out_time' => '19:00:00',
            'status' => 'pending',
            'comment' => 'test',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        $response = $this->actingAs($admin, 'admin')->post("/admin/attendance/approve", [
            'id' => $attendance->id,
            'attendance_request_id' => $attendanceRequest->id,
        ]);

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $attendanceRequest->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in_time' => '10:00:00',
            'clock_out_time' => '19:00:00',
        ]);
    }
}
