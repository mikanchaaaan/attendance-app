<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class GetAdminStaffList extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */

    // ユーザー情報取得機能(管理者) - 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function testAdminViewStaffList()
    {
        $users = User::factory(10)->create([
            'email_verified_at' => now(),
        ]);

        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin_password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        $response = $this->actingAs($admin, 'admin')->get('/admin/staff/list');
        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    // ユーザー情報取得機能(管理者) - ユーザーの勤怠情報が正しく表示される
    public function testAdminViewStaffAttendance()
    {
        $users = User::factory(10)->create([
            'email_verified_at' => now(),
        ]);

        foreach ($users as $user) {
            $attendances = Attendance::factory(10)->withRest()->create([
                'user_id' => $user->id,
                'date' => Carbon::now()
            ]);
        }

        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin_password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        $targetUser = $users->first();
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/staff/{$targetUser->id}");
        $response->assertStatus(200);

        foreach ($attendances->where('user_id', $targetUser->id) as $attendance) {
            $attendanceDate = Carbon::parse($attendance->date)->format('Y/m');
            $response->assertSee($attendanceDate);
            $response->assertSee(Carbon::parse($attendance->clock_in_time)->format('H:i'));
            $response->assertSee(Carbon::parse($attendance->clock_out_time)->format('H:i'));
        }
    }

    // ユーザー情報取得機能(管理者) - 「前月」を押下した時に表示月の前月の情報が表示される
    public function testAdminViewStaffAttendancePreviousMonth()
    {
        $users = User::factory(10)->create([
            'email_verified_at' => now(),
        ]);

        foreach ($users as $user) {
            $attendances = Attendance::factory(10)->withRest()->create([
                'user_id' => $user->id,
                'date' => Carbon::now()->subMonth(),
            ]);
        }

        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin_password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        $targetUser = $users->first();
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/staff/{$targetUser->id}");
        $response->assertStatus(200);

        $previousMonth = Carbon::now()->subMonth();
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $targetUser->id . '?month=' . $previousMonth->format('Y-m'));
        $response->assertStatus(200);

        foreach ($attendances->where('user_id', $targetUser->id) as $attendance) {
            $previousMonth = Carbon::now()->subMonth()->format('Y/m');
            $response->assertSee($previousMonth);
            $response->assertSee(Carbon::parse($attendance->clock_in_time)->format('H:i'));
            $response->assertSee(Carbon::parse($attendance->clock_out_time)->format('H:i'));
        }

        foreach ($attendances->where('user_id', $targetUser->id) as $attendance) {
            $thisMonth = Carbon::now()->format('Y/m');
            $response->assertDontSee($thisMonth);
        }
    }

    // ユーザー情報取得機能(管理者) - 「翌月」を押下した時に表示月の翌月の情報が表示される
    public function testAdminViewStaffAttendanceNextMonth()
    {
        $users = User::factory(10)->create([
            'email_verified_at' => now(),
        ]);

        foreach ($users as $user) {
            $attendances = Attendance::factory(10)->withRest()->create([
                'user_id' => $user->id,
                'date' => Carbon::now()->addMonth(),
            ]);
        }

        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin_password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        // 5. ユーザーの勤怠一覧ページにアクセス
        $targetUser = $users->first();
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/staff/{$targetUser->id}");
        $response->assertStatus(200);

        $nextMonth = Carbon::now()->addMonth();
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $targetUser->id . '?month=' . $nextMonth->format('Y-m'));
        $response->assertStatus(200);

        foreach ($attendances->where('user_id', $targetUser->id) as $attendance) {
            $nextMonth = Carbon::now()->subMonth()->format('Y/m');
            $response->assertSee($nextMonth);
            $response->assertSee(Carbon::parse($attendance->clock_in_time)->format('H:i'));
            $response->assertSee(Carbon::parse($attendance->clock_out_time)->format('H:i'));
        }

        foreach ($attendances->where('user_id', $targetUser->id) as $attendance) {
            $thisMonth = Carbon::now()->format('Y/m');
            $response->assertDontSee($thisMonth);
        }
    }

    // ユーザー情報取得機能(管理者) - 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function testAdminViewStaffAttendanceDetail()
    {
        $users = User::factory(10)->create([
            'email_verified_at' => now(),
        ]);

        foreach ($users as $user) {
            $attendances = Attendance::factory(10)->withRest()->create([
                'user_id' => $user->id,
                'date' => Carbon::now()->addMonth(),
            ]);
        }

        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin_password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        $targetUser = $users->first();
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/staff/{$targetUser->id}");
        $response->assertStatus(200);

        $response->assertSee('詳細');

        $attendance = $attendances->first();
        $response = $this->actingAs($admin, 'admin')->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($attendance->clock_in_time)->format('H:i'));
        $response->assertSee(Carbon::parse($attendance->clock_out_time)->format('H:i'));
        $response->assertSee($user->name);
    }
}