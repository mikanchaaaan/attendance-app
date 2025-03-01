<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;


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
        $clockInTime = $this->faker->time('H:i:s', strtotime('00:00:00'));
        $clockInTimestamp = strtotime($clockInTime);

        $clockInPlus12HoursTimestamp = $clockInTimestamp + 43200;

        $clockOutTimestamp = rand(
            $clockInTimestamp + 14400,
            $clockInPlus12HoursTimestamp
        );

        if ($clockOutTimestamp >= strtotime('24:00:00', $clockInTimestamp)) {
            $clockOutTimestamp = strtotime('23:59:59', $clockInTimestamp);
        }

        $clockOutTime = date('H:i:s', $clockOutTimestamp);

        return [
            'clock_in_time' => $clockInTime,
            'clock_out_time' => $clockOutTime,
        ];
    }

    public function withRest(int $restCount = 2): self
    {
        return $this->afterCreating(function (Attendance $attendance) use ($restCount) {
            $clockInTime = $attendance->clock_in_time;
            $clockOutTime = $attendance->clock_out_time;

            $clockInTimestamp = strtotime($clockInTime);
            $clockOutTimestamp = strtotime($clockOutTime);

            $workDuration = $clockOutTimestamp - $clockInTimestamp;
            $maxRestDuration = max(1800, $workDuration - 7200);

            $restCount = min($restCount, 2);

            $previousRestOutTimestamp = $clockInTimestamp;
            $totalRestDuration = 0;

            for ($i = 0; $i < $restCount; $i++) {
                $restInMin = $previousRestOutTimestamp + 1800;
                $restInMax = max($restInMin, $clockOutTimestamp - 7200);

                if ($restInMin >= $restInMax) {
                    break;
                }

                $restInTimestamp = rand($restInMin, $restInMax);
                $restInTime = date('H:i:s', $restInTimestamp);
                $restOutMin = $restInTimestamp + 1800;
                $restOutMax = max($restOutMin, $clockOutTimestamp - 3600);

                if ($restOutMin >= $restOutMax) {
                    break;
                }

                $restOutTimestamp = rand($restOutMin, $restOutMax);
                $restOutTime = date('H:i:s', $restOutTimestamp);

                if ($restOutTimestamp <= $restInTimestamp) {
                    break;
                }

                $rests = Rest::create([
                    'rest_in_time' => $restInTime,
                    'rest_out_time' => $restOutTime,
                ]);

                $previousRestOutTimestamp = $restOutTimestamp;
                $totalRestDuration += ($restOutTimestamp - $restInTimestamp);

                if ($totalRestDuration >= $maxRestDuration) {
                    break;
                }

                if (!empty($rests)) {
                    $attendance->rests()->attach($rests);
                }
            }
        });
    }
}
