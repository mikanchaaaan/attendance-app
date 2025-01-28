<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rest>
 */
class RestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 既存のAttendanceをランダムに取得
        $attendance = Attendance::inRandomOrder()->first();  // ランダムに1件取得

        // 出勤時間を取得
        $clockInTime = strtotime($attendance->clock_in_time);
        $clockOutTime = strtotime($attendance->clock_out_time);

        // 休憩開始時間を出勤時間からランダムに決定（出勤から2時間以内などに設定）
        $restInTime = date('H:i:s', rand($clockInTime, $clockOutTime - 1));

        // 休憩終了時間を休憩開始時間からランダムに決定（休憩時間は1時間～勤務時間の残り時間以内）
        // 勤務時間内に収めるため、休憩終了時間は退勤時間を越えないように調整
        $maxRestDuration = $clockOutTime - strtotime($restInTime);  // 休憩終了時間の最大時間は退勤時間から休憩開始時間まで
        $restOutTime = date('H:i:s', min(strtotime($restInTime) + rand(3600, min(28800, $maxRestDuration)), $clockOutTime));

        return [
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'rest_in_time' => $restInTime,
            'rest_out_time' => $restOutTime,
        ];
    }
}
