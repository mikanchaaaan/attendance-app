<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class GetAdminAttendanceDetail extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    // 勤怠詳細情報取得機能(管理者) - 勤怠詳細画面に表示されるデータが選択したものになっている
    public function testAdminDetailView()
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

        $attendance = $attendances->first();
        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response->assertSee($attendance->user->name);
        $response->assertSee(Carbon::parse($attendance->clock_in_time)->format('H:i'));
        $response->assertSee(Carbon::parse($attendance->clock_out_time)->format('H:i'));
    }

    // 勤怠詳細情報取得機能（管理者） - 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testAdminClockValidation()
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

        $attendance = $attendances->first();
        $response = $this->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->post("/admin/attendance/update", [
            'clock_in_time' => '19:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $response->assertSessionHasErrors([
            'clock_in_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    // 勤怠詳細情報取得機能（管理者） - 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testAdminRestInValidation()
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

        $selectedDate = Carbon::now()->subDays(3);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        $attendance = $attendances->first();
        $response = $this->actingAs($admin, 'admin')->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $invalidRestInTime = '20:00';

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

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/update', $formData);
        $response->assertRedirect("/attendance/{$attendance->id}");

        $response->assertSessionHasErrors('rests.0.rest_in_time');
        $errors = session('errors')->getBag('default')->getMessages();

        $this->assertContains('休憩時間が勤務時間外です', $errors['rests.0.rest_in_time']);
    }

    // 勤怠詳細情報取得機能（管理者） - 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testAdminRestOutValidation()
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

        $selectedDate = Carbon::now()->subDays(3);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin_password',
        ]);
        $response->assertRedirect('/admin/attendance/list');

        $attendance = $attendances->first();
        $response = $this->actingAs($admin, 'admin')->get("/attendance/{$attendance->id}");
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

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/update', $formData);
        $response->assertRedirect("/attendance/{$attendance->id}");

        $response->assertSessionHasErrors('rests.0.rest_out_time');
        $errors = session('errors')->getBag('default')->getMessages();

        $this->assertContains('休憩時間が勤務時間外です', $errors['rests.0.rest_out_time']);
    }

    // 勤怠詳細情報取得機能（管理者） - 備考欄が未入力の場合のエラーメッセージが表示される
    public function testAdminCommentValidation()
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

        $selectedDate = Carbon::now()->subDays(3);

        $attendance = $attendances->first();
        $response = $this->actingAs($admin)->get("/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $formData = [
            'date' => $selectedDate->format('Y-m-d'),
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
            'comment' => Null,
        ];

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/update', $formData);
        $response->assertRedirect("/attendance/{$attendance->id}");

        $response->assertSessionHasErrors('comment');
        $errors = session('errors')->getBag('default')->getMessages();

        $this->assertContains('備考を記入してください', $errors['comment']);
    }
}
