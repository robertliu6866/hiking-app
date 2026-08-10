<?php

namespace Tests\Feature;

use App\Livewire\TripJoinControl;
use App\Livewire\WishJoinControl;
use App\Models\Trip;
use App\Models\TripWish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireJoinControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_join_trip_without_page_reload(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '合歡北峰',
            'mountain' => '合歡北峰',
            'category' => '百岳',
            'location' => '南投',
            'departure_time' => now()->addWeek(),
            'quota' => 6,
            'status' => 'open',
        ]);

        $this->actingAs($user);

        Livewire::test(TripJoinControl::class, [
            'tripId' => $trip->id,
            'variant' => 'card',
        ])->call('toggle');

        $this->assertTrue($trip->participants()->whereKey($user->id)->exists());
    }

    public function test_user_can_cancel_trip_join_without_page_reload(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '合歡北峰',
            'mountain' => '合歡北峰',
            'category' => '百岳',
            'location' => '南投',
            'departure_time' => now()->addWeek(),
            'quota' => 6,
            'status' => 'open',
        ]);
        $trip->participants()->attach($user->id);

        $this->actingAs($user);

        Livewire::test(TripJoinControl::class, [
            'tripId' => $trip->id,
            'variant' => 'card',
        ])->call('toggle');

        $this->assertFalse($trip->participants()->whereKey($user->id)->exists());
    }

    public function test_trip_detail_join_button_toggles_to_cancel_and_back(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '合歡北峰',
            'mountain' => '合歡北峰',
            'category' => '百岳',
            'location' => '南投',
            'departure_time' => now()->addWeek(),
            'quota' => 6,
            'status' => 'open',
        ]);

        $this->actingAs($user);

        Livewire::test(TripJoinControl::class, [
            'tripId' => $trip->id,
            'variant' => 'detail',
        ])
            ->assertSee('我要參加')
            ->call('toggle')
            ->assertSee('取消報名')
            ->assertSee('1 / 6')
            ->call('toggle')
            ->assertSee('我要參加')
            ->assertSee('0 / 6');
    }

    public function test_trip_detail_shows_all_joined_members(): void
    {
        $host = User::factory()->create();
        $firstUser = User::factory()->create(['name' => '第一位山友']);
        $secondUser = User::factory()->create(['name' => '第二位山友']);
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '合歡北峰',
            'mountain' => '合歡北峰',
            'category' => '百岳',
            'location' => '南投',
            'departure_time' => now()->addWeek(),
            'quota' => 6,
            'status' => 'open',
        ]);
        $trip->participants()->attach([$firstUser->id, $secondUser->id]);

        $this->actingAs($firstUser);

        Livewire::test(TripJoinControl::class, [
            'tripId' => $trip->id,
            'variant' => 'detail',
        ])
            ->assertSee('已報名山友')
            ->assertSee('第一位山友')
            ->assertSee('第二位山友')
            ->assertSee('2 人');
    }

    public function test_trip_join_route_toggles_for_non_livewire_requests(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '合歡北峰',
            'mountain' => '合歡北峰',
            'category' => '百岳',
            'location' => '南投',
            'departure_time' => now()->addWeek(),
            'quota' => 6,
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->post(route('trips.join', $trip))
            ->assertRedirect()
            ->assertSessionHas('status', 'trip-joined');

        $this->assertTrue($trip->participants()->whereKey($user->id)->exists());

        $this->actingAs($user)
            ->post(route('trips.join', $trip))
            ->assertRedirect()
            ->assertSessionHas('status', 'trip-canceled');

        $this->assertFalse($trip->participants()->whereKey($user->id)->exists());
    }


    public function test_user_can_plus_one_wish_without_page_reload(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $wish = TripWish::create([
            'user_id' => $owner->id,
            'mountain' => '羊頭山',
            'wished_date' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($user);

        Livewire::test(WishJoinControl::class, [
            'wishId' => $wish->id,
        ])->call('toggle');

        $this->assertTrue($wish->users()->whereKey($user->id)->exists());
        $this->assertDatabaseHas('trip_wish_user', [
            'trip_wish_id' => $wish->id,
            'user_id' => $user->id,
            'status' => 'joined',
        ]);
    }

    public function test_wish_button_toggles_to_cancel_and_back(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $wish = TripWish::create([
            'user_id' => $owner->id,
            'mountain' => '羊頭山',
            'wished_date' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($user);

        Livewire::test(WishJoinControl::class, [
            'wishId' => $wish->id,
        ])
            ->assertSee('+0')
            ->call('toggle')
            ->assertSee('取消')
            ->call('toggle')
            ->assertSee('+0');
    }

    public function test_user_can_cancel_wish_plus_one_without_page_reload(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $wish = TripWish::create([
            'user_id' => $owner->id,
            'mountain' => '羊頭山',
            'wished_date' => now()->addMonth()->toDateString(),
        ]);
        $wish->users()->attach($user->id);

        $this->actingAs($user);

        Livewire::test(WishJoinControl::class, [
            'wishId' => $wish->id,
        ])->call('toggle');

        $this->assertFalse($wish->users()->whereKey($user->id)->exists());
        $this->assertDatabaseHas('trip_wish_user', [
            'trip_wish_id' => $wish->id,
            'user_id' => $user->id,
            'status' => 'canceled',
        ]);
    }

    public function test_wish_plus_one_route_toggles_for_non_livewire_requests(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $wish = TripWish::create([
            'user_id' => $owner->id,
            'mountain' => '羊頭山',
            'wished_date' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($user)
            ->post(route('trip-wishes.join', $wish))
            ->assertRedirect()
            ->assertSessionHas('status', 'wish-joined');

        $this->assertTrue($wish->users()->whereKey($user->id)->exists());
        $this->assertDatabaseHas('trip_wish_user', [
            'trip_wish_id' => $wish->id,
            'user_id' => $user->id,
            'status' => 'joined',
        ]);

        $this->actingAs($user)
            ->post(route('trip-wishes.join', $wish))
            ->assertRedirect()
            ->assertSessionHas('status', 'wish-canceled');

        $this->assertFalse($wish->users()->whereKey($user->id)->exists());
        $this->assertDatabaseHas('trip_wish_user', [
            'trip_wish_id' => $wish->id,
            'user_id' => $user->id,
            'status' => 'canceled',
        ]);

        $this->actingAs($user)
            ->post(route('trip-wishes.join', $wish))
            ->assertRedirect()
            ->assertSessionHas('status', 'wish-joined');

        $this->assertTrue($wish->users()->whereKey($user->id)->exists());
        $this->assertDatabaseHas('trip_wish_user', [
            'trip_wish_id' => $wish->id,
            'user_id' => $user->id,
            'status' => 'joined',
        ]);
    }
}
