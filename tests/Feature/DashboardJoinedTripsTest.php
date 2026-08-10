<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\TripWish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardJoinedTripsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_joined_trips(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '羊頭山單攻',
            'mountain' => '羊頭山',
            'category' => '百岳',
            'location' => '花蓮',
            'departure_time' => now()->addWeek(),
            'meeting_point' => '台北車站',
            'quota' => 6,
            'status' => 'open',
        ]);

        $trip->participants()->attach($user->id);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('我的報名')
            ->assertSee('已報名')
            ->assertSee('羊頭山單攻')
            ->assertSee('台北車站');
    }

    public function test_dashboard_hides_past_joined_trips(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();

        $pastTrip = Trip::create([
            'user_id' => $host->id,
            'title' => '昨天的行程',
            'mountain' => '過去的山',
            'location' => '花蓮',
            'departure_time' => now()->subDay(),
            'quota' => 6,
            'status' => 'open',
        ]);

        $futureTrip = Trip::create([
            'user_id' => $host->id,
            'title' => '明天的行程',
            'mountain' => '未來的山',
            'location' => '花蓮',
            'departure_time' => now()->addDay(),
            'quota' => 6,
            'status' => 'open',
        ]);

        $pastTrip->participants()->attach($user->id);
        $futureTrip->participants()->attach($user->id);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('明天的行程')
            ->assertDontSee('昨天的行程');
    }

    public function test_dashboard_shows_joined_wishes_with_other_participants(): void
    {
        $host = User::factory()->create(['name' => '發起山友']);
        $user = User::factory()->create(['name' => '自己']);
        $partner = User::factory()->create(['name' => '同行山友']);
        $wish = TripWish::create([
            'user_id' => $host->id,
            'mountain' => '雪山',
            'wished_date' => now()->addWeek()->toDateString(),
            'note' => '下週想找人一起雪山單攻',
        ]);

        $wish->allUsers()->attach($user->id, ['status' => 'joined']);
        $wish->allUsers()->attach($partner->id, ['status' => 'joined']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('我的許願')
            ->assertSee('雪山')
            ->assertSee('下週想找人一起雪山單攻')
            ->assertSee('同行山友')
            ->assertSee('2 人響應');
    }
}
