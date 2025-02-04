<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Log;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 出勤時間を生成
        $clockInTime = $this->faker->time('H:i:s', strtotime('00:00:00'));
        $clockInTimestamp = strtotime($clockInTime); // 出勤時間のタイムスタンプを取得

        // 出勤日の終了時間（23:59:59）を計算
        $endOfDayTimestamp = strtotime('23:59:59', $clockInTimestamp);

        // 出勤時間から12時間後のタイムスタンプを計算
        $clockInPlus12HoursTimestamp = $clockInTimestamp + 43200;  // 出勤時間の12時間後

        // 退勤時間を計算（出勤から4時間後〜12時間以内で、出勤日内に収める）
        $clockOutTimestamp = rand(
            $clockInTimestamp + 14400,  // 出勤時間の4時間後
            $clockInPlus12HoursTimestamp  // 出勤時間から12時間以内、23:59:59まで
        );

        // タイムスタンプから退勤時間をフォーマット
        $clockOutTime = date('H:i:s', $clockOutTimestamp);

        return [
            'clock_in_time' => $clockInTime,
            'clock_out_time' => $clockOutTime,
        ];
    }

    // 出勤データと一緒に休憩データも作る
public function withRest(int $restCount = 2): self
{
    return $this->afterCreating(function (Attendance $attendance) use ($restCount) {
        $clockInTime = $attendance->clock_in_time;
        $clockOutTime = $attendance->clock_out_time;

        // 出勤時間と退勤時間をタイムスタンプに変換
        $clockInTimestamp = strtotime($clockInTime);
        $clockOutTimestamp = strtotime($clockOutTime);

        // 勤務時間の長さを取得**
        $workDuration = $clockOutTimestamp - $clockInTimestamp; // 勤務時間（秒）

        // 最大休憩時間を「勤務時間 - 2時間」に設定**
        $maxRestDuration = max(1800, $workDuration - 7200); // 2時間 = 7200秒、最低30分（1800秒）は確保

        // 休憩回数の上限チェック**
        $restCount = min($restCount, 2);  // 例: 最大2回まで

        $previousRestOutTimestamp = $clockInTimestamp; // 最初の休憩は出勤時間以降で開始
        $totalRestDuration = 0; // 休憩時間の合計

        for ($i = 0; $i < $restCount; $i++) {
            // **休憩開始時間 (`restInTime`) を決定**
            // 直前の休憩終了時間から **30分後以降** にする（連続休憩を防ぐ）
            $restInMin = $previousRestOutTimestamp + 1800;  // 前回の休憩終了時間の30分後
            $restInMax = max($restInMin, $clockOutTimestamp - 7200);  // 退勤2時間前まで

            if ($restInMin >= $restInMax) {
                break;
            }

            $restInTimestamp = rand($restInMin, $restInMax);
            $restInTime = date('H:i:s', $restInTimestamp);

            // ** 休憩終了時間 (`restOutTime`) を決定**
            // 休憩開始時間から **30分後～2時間以内** にする
            $restOutMin = $restInTimestamp + 1800;  // 休憩開始30分後
            $restOutMax = max($restOutMin, $clockOutTimestamp - 3600);  // 退勤1時間前まで

            if ($restOutMin >= $restOutMax) {
                break;
            }

            $restOutTimestamp = rand($restOutMin, $restOutMax);
            $restOutTime = date('H:i:s', $restOutTimestamp);

            // ** 休憩時間が異常な場合はスキップ**
            if ($restOutTimestamp <= $restInTimestamp) {
                break;
            }

            // ** 休憩データの作成**
            $rests = Rest::create([
                'rest_in_time' => $restInTime,
                'rest_out_time' => $restOutTime,
            ]);

            // ** 次の休憩の開始時間を更新**
            $previousRestOutTimestamp = $restOutTimestamp;

            // **🔹 休憩時間の合計を加算**
            $totalRestDuration += ($restOutTimestamp - $restInTimestamp);

            // **🔹 休憩時間が「勤務時間 - 2時間」以内に収まるようにする**
            if ($totalRestDuration >= $maxRestDuration) {
                break;
            }

            // **中間テーブル（attendance_rest）に休憩を登録**
            if (!empty($rests)) {
                $attendance->rests()->attach($rests);
            }
        }

    });
}


}
