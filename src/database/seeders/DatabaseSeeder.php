<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $user = User::first();

        $existingDates = Attendance::pluck('date')->toArray();
        $startDate = now()->subMonths(6);
        $endDate = now();

        $dates = [];
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');

            if (!in_array($dateStr, $existingDates)) {
                $dates[] = $dateStr;
            }
        }

        $dates = array_slice($dates, 0, 180);

        sort($dates);

        $attendances = Attendance::factory(180)->withRest()->create([
            'user_id' => $user->id,
            'date' => function () use (&$dates) {
                return array_shift($dates);
            }
        ]);
    }
}
