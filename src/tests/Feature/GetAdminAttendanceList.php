<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class GetAdminAttendanceList extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    // 勤怠一覧情報取得機能（管理者） - その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function testViewAdminAttendanceList()
    {
        $users = User::factory(10)->create([
            'email_verified_at' => now(),
        ]);

        foreach ($users as $user) {
            Attendance::factory(10)->withRest()->create([
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

        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->clock_in_time);
            $response->assertSee($user->clock_out_time);
        }
    }

    // 勤怠一覧情報取得機能（管理者） - 遷移した際に現在の日付が表示される
    public function testViewAdminAttendanceDate()
    {
        $users = User::factory(10)->create([
            'email_verified_at' => now(),
        ]);

        foreach ($users as $user) {
            Attendance::factory(10)->withRest()->create([
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

        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        $todayDate = Carbon::now()->format('Y/m/d');
        $response->assertSee($todayDate);
    }

    // 勤怠一覧情報取得機能（管理者） - 「前日」を押下した時に前の日の勤怠情報が表示される
    public function testViewAttendanceListPreviousDay()
    {
        $users = User::factory(10)->create([
            'email_verified_at' => now(),
        ]);

        $today = Carbon::now();
        $previousDay = Carbon::yesterday();

        foreach ($users as $user) {
            Attendance::factory(5)->withRest()->create([
                'user_id' => $user->id,
                'date' => $today
            ]);
        }

        $users->each(function ($user) use ($previousDay) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $previousDay
            ]);
        });

        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin_password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);

        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($today->format('Y/m/d'));

        $response = $this->post('/admin/attendance/list', ['date' => $previousDay->format('Y/m/d')]);
        $response->assertSee($previousDay->format('Y/m/d'));
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->clock_in_time);
            $response->assertSee($user->clock_out_time);
        }
    }

    // 勤怠一覧情報取得機能（管理者） - 「翌日」を押下した時に前の日の勤怠情報が表示される
    public function testViewAttendanceListNextNextDay()
    {
        $users = User::factory(10)->create([
            'email_verified_at' => now(),
        ]);

        $today = Carbon::now();
        $nextDay = Carbon::tomorrow();

        foreach ($users as $user) {
            Attendance::factory(5)->withRest()->create([
                'user_id' => $user->id,
                'date' => $today
            ]);
        }

        $users->each(function ($user) use ($nextDay) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $nextDay
            ]);
        });

        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin_password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);

        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($today->format('Y/m/d'));

        $response = $this->post('/admin/attendance/list', ['date' => $nextDay->format('Y/m/d')]);
        $response->assertSee($nextDay->format('Y/m/d'));

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->clock_in_time);
            $response->assertSee($user->clock_out_time);
        }
    }
}
