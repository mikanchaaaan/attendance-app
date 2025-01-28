<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        // 1. 既存の日付を取得し、重複しない日付リストを作成
        $existingDates = Attendance::pluck('date')->toArray(); // DBから既存の日付を取得
        $startDate = now()->subMonths(3); // 3ヶ月前
        $endDate = now()->addMonths(3); // 3ヶ月後

        // 重複しない日付リストを生成
        $dates = [];
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');

            // 既存の日付リストに含まれていなければ追加
            if (!in_array($dateStr, $existingDates)) {
                $dates[] = $dateStr;
            }
        }

        // 日付リストが180個未満の場合、数を補填する処理も入れる
        $dates = array_slice($dates, 0, 180); // 最大180個までに制限

        // 日付順に並べ替え
        sort($dates);

        // 2. ファクトリを実行して重複しない日付を使用
        Attendance::factory()->count(180)->create([
            'user_id' => 2,  // Test UserのIDを設定
            'date' => function () use (&$dates) {
                // 日付リストからランダムに選ぶ
                return array_shift($dates);  // 選んだ日付をリストから削除
            }
        ]);

        Rest::factory()->count(180)->create([  // 例えば2回の休憩を作成
            'user_id' => 2,  // Test UserのIDを設定
        ]);

    }
}
