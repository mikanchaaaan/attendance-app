<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class GetAdminAttendanceDetail extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    // 勤怠詳細情報取得機能 - 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function testDetailDisplayName()
    {

    }
}
