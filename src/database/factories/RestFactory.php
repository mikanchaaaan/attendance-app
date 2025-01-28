<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Rest;
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
        $attendance = Attendance::inRandomOrder()->first();

        // 出勤時間と退勤時間を取得
        $clockInTime = strtotime($attendance->clock_in_time);
        $clockOutTime = strtotime($attendance->clock_out_time);

        // 勤務時間を計算（秒単位）
        $workDuration = $clockOutTime - $clockInTime;

        // 勤務時間が1時間未満の場合は休憩を作成しない
        if ($workDuration < 3600) {
            return [];
        }

        // 既存の休憩時間を計算
        $existingRestDuration = Rest::where('attendance_id', $attendance->id)
            ->get()
            ->reduce(function ($carry, $rest) {
                $restIn = strtotime($rest->rest_in_time);
                $restOut = strtotime($rest->rest_out_time);
                return $carry + ($restOut - $restIn);
            }, 0);

        // 残りの勤務時間（秒単位）
        $remainingWorkTime = $workDuration - $existingRestDuration;

        // 残りの勤務時間が30分（1800秒）未満の場合は休憩を作成しない
        if ($remainingWorkTime < 1800) {
            return [];
        }

        // 休憩開始時間をランダムに設定（出勤時間から退勤時間の間）
        $latestPossibleRestStart = $clockOutTime - min(3600, $remainingWorkTime); // 残り時間内に収まる最大休憩時間
        $restInTime = date('H:i:s', rand($clockInTime, $latestPossibleRestStart));

        // 休憩終了時間を設定（最大1時間、残り勤務時間内に収める）
        $maxRestDuration = min(3600, $remainingWorkTime); // 最大1時間または残り勤務時間
        $restOutTime = date('H:i:s', strtotime($restInTime) + rand(1800, $maxRestDuration));

        return [
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'rest_in_time' => $restInTime,
            'rest_out_time' => $restOutTime,
        ];
    }
}
