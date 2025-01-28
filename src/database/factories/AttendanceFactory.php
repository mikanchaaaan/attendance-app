<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;

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

        // 退勤時間を出勤時間からランダムに決定
        $clockOutTime = date('H:i:s', min(strtotime($clockInTime) + rand(3600, 28800), strtotime('23:59:59')));

        return [
            'user_id' => 2,  // ユーザーID（仮に2を使用）
            'date' => $this->faker->dateTimeBetween('-3 months', '+3 months')->format('Y-m-d'),  // 重複しない日付
            'clock_in_time' => $clockInTime,
            'clock_out_time' => $clockOutTime,
        ];
    }
}
