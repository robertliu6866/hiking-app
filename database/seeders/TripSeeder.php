<?php

namespace Database\Seeders;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        $host = User::first()
            ?? User::factory()->create([
                'name' => 'Robert',
                'email' => 'robert@example.com',
            ]);

        $trips = [
            [
                'title' => '合歡北峰日出單攻',
                'mountain' => '合歡北峰',
                'category' => '百岳',
                'route_mode' => 'single',
                'difficulty' => 3,
                'distance_km' => 4.8,
                'elevation_gain_m' => 470,
                'estimated_hours' => 4.5,
                'route_details' => [
                    'trailhead' => '小風口',
                    'summit' => '合歡北峰',
                    'turnaround_time' => '08:30',
                ],
                'location' => '南投',
                'departure_time' => now()->addDays(7)->setTime(4, 30),
                'meeting_point' => '小風口停車場',
                'price' => 300,
                'quota' => 8,
                'status' => 'open',
                'description' => '慢速上行，看天候調整節奏，需自備頭燈與保暖層。',
            ],
            [
                'title' => '南湖群峰三日縱走',
                'mountain' => '南湖大山',
                'category' => '百岳',
                'route_mode' => 'traverse',
                'difficulty' => 5,
                'distance_km' => 37.2,
                'elevation_gain_m' => 2800,
                'estimated_hours' => 26,
                'route_details' => [
                    'start_point' => '勝光登山口',
                    'end_point' => '勝光登山口',
                    'waypoints' => ['雲稜山莊', '審馬陣山', '南湖北山', '南湖圈谷'],
                ],
                'location' => '宜蘭',
                'departure_time' => now()->addDays(18)->setTime(6, 0),
                'meeting_point' => '宜蘭轉運站',
                'price' => 1200,
                'quota' => 8,
                'status' => 'open',
                'description' => '多日重裝行程，需有百岳經驗與穩定體能，依山屋與天候調整。',
            ],
            [
                'title' => '陽明山自由訓練走',
                'mountain' => '七星山系',
                'category' => '訓練',
                'route_mode' => 'custom',
                'difficulty' => 2,
                'distance_km' => 8,
                'elevation_gain_m' => 520,
                'estimated_hours' => 3.5,
                'route_details' => [
                    'plan_note' => '依天候與參與者狀況，現場決定是否加走夢幻湖。',
                ],
                'location' => '台北',
                'departure_time' => now()->addDays(3)->setTime(14, 0),
                'meeting_point' => '小油坑遊客中心',
                'price' => 0,
                'quota' => 6,
                'status' => 'open',
                'description' => '輕量行程，適合想認識路線與新山友的人。',
            ],
        ];

        foreach ($trips as $tripData) {
            $trip = Trip::updateOrCreate(
                ['title' => $tripData['title']],
                ['user_id' => $host->id, ...$tripData],
            );

            $trip->participants()->syncWithoutDetaching($host->id);
        }
    }
}
