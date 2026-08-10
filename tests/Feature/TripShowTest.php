<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_show_displays_basic_activity_details(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '合歡北峰日出團',
            'mountain' => '合歡山北峰',
            'category' => '百岳',
            'route_mode' => 'single',
            'difficulty' => 2,
            'distance_km' => 5.8,
            'elevation_gain_m' => 520,
            'estimated_hours' => 4.5,
            'route_details' => [
                'trailhead' => '小風口',
                'summit' => '合歡山北峰',
                'turnaround_time' => '11:00',
                'suitable_for' => '有郊山經驗、可行走 5 小時',
                'equipment' => ['雨衣', '頭燈', '保暖層'],
                'safety_note' => '若 11:00 未抵達稜線即折返。',
                'cancellation_policy' => '豪雨特報或颱風警報即取消。',
            ],
            'location' => '南投',
            'departure_time' => now()->addWeek(),
            'meeting_point' => '小風口停車場',
            'price' => 500,
            'quota' => 6,
            'description' => '請準時集合。',
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->get(route('trips.show', $trip))
            ->assertOk()
            ->assertSee('活動詳情')
            ->assertSee('合歡北峰日出團')
            ->assertSee('小風口停車場')
            ->assertSee('NT$ 500')
            ->assertSee('請準時集合。');
    }

    public function test_trip_show_renders_standard_join_form(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '合歡北峰日出單攻',
            'mountain' => '合歡北峰',
            'category' => '百岳',
            'location' => '南投',
            'departure_time' => now()->addWeek(),
            'quota' => 6,
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->get(route('trips.show', $trip))
            ->assertOk()
            ->assertSee('method="POST"', false)
            ->assertSee(route('trips.join', $trip), false)
            ->assertSee('我要報名');
    }
}
