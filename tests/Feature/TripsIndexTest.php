<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_trips_index_renders_simple_activity_list(): void
    {
        $host = User::factory()->create(['name' => '主辦山友']);
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '合歡北峰日出團',
            'mountain' => '合歡山北峰',
            'category' => '百岳',
            'route_mode' => 'single',
            'difficulty' => 2,
            'location' => '南投',
            'departure_time' => now()->addWeek(),
            'quota' => 6,
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->get(route('trips.index'))
            ->assertOk()
            ->assertSee('活動列表')
            ->assertSee('選一個活動報名')
            ->assertSee('合歡北峰日出團')
            ->assertSee('合歡山北峰')
            ->assertSee('看詳情');
    }

    public function test_trips_index_only_shows_upcoming_trips(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();

        Trip::create([
            'user_id' => $host->id,
            'title' => '已過期行程',
            'mountain' => '過去的山',
            'category' => '百岳',
            'location' => '南投',
            'departure_time' => now()->subDay(),
            'quota' => 6,
            'status' => 'open',
        ]);

        Trip::create([
            'user_id' => $host->id,
            'title' => '未來行程',
            'mountain' => '未來的山',
            'category' => '百岳',
            'location' => '南投',
            'departure_time' => now()->addDay(),
            'quota' => 6,
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->get(route('trips.index'))
            ->assertOk()
            ->assertSee('未來的山')
            ->assertDontSee('過去的山');
    }

    public function test_user_can_join_from_trip_detail_and_see_status_on_index(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '新手郊山團',
            'mountain' => '七星山',
            'location' => '台北',
            'departure_time' => now()->addDay(),
            'quota' => 6,
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->post(route('trips.join', $trip))
            ->assertRedirect()
            ->assertSessionHas('status', 'trip-joined');

        $this->actingAs($user)
            ->get(route('trips.index'))
            ->assertOk()
            ->assertSee('已報名');
    }
}
