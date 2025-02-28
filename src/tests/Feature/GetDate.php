<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class GetDate extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    // 日時取得機能 - 現在の日時がUIと同じ形で表示されている
    public function testGetDate()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance'); // ログイン状態でアクセス
        $response->assertStatus(200);

        $todayView = Carbon::now()->isoFormat('YYYY年M月D日（dd）');
        $currentTimeView = Carbon::now()->format('H:i'); // 'HH:MM'

        $response->assertSee($todayView)->assertSee($currentTimeView);
    }
}
