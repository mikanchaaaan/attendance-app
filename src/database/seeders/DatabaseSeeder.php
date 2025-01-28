<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);

        // attendanceのファクトリ
        $existingDates = Attendance::pluck('date')->toArray();
        $startDate = now()->subMonths(3);
        $endDate = now()->addMonths(3);

        $dates = [];
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');

            if (!in_array($dateStr, $existingDates)) {
                $dates[] = $dateStr;
            }
        }

        $dates = array_slice($dates, 0, 180);

        sort($dates);

        $attendances = Attendance::factory()->count(180)->create([
            'user_id' => 2,
            'date' => function () use (&$dates) {
                return array_shift($dates);
            }
        ]);

        // Step 2: Restデータを作成
        Rest::factory()->count(180)->create(function () {
            $attendance = Attendance::inRandomOrder()->first();

            return [
                'attendance_id' => $attendance->id, // 必須
                'user_id' => $attendance->user_id,
            ];
        });

    }
}
