<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTripRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_trip_registration_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $host = User::factory()->create();
        $participant = User::factory()->create([
            'name' => '報名會員',
            'phone' => '0912345678',
        ]);

        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '合歡北峰日出團',
            'mountain' => '合歡山北峰',
            'location' => '南投',
            'departure_time' => now()->addWeek(),
            'quota' => 6,
            'status' => 'open',
        ]);

        $trip->participants()->attach($participant->id);

        $this->actingAs($admin)
            ->get(route('admin.trips.index'))
            ->assertOk()
            ->assertSee('活動報名名單')
            ->assertSee('合歡北峰日出團')
            ->assertSee('報名會員')
            ->assertSee('0912345678');
    }

    public function test_non_admin_cannot_view_trip_registration_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.trips.index'))
            ->assertForbidden();
    }
}
