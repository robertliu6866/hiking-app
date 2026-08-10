<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $host = User::factory()->create();
        $member = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '雪山東峰',
            'mountain' => '雪山',
            'location' => '苗栗',
            'departure_time' => now()->addWeek(),
            'quota' => 8,
            'status' => 'open',
        ]);
        $trip->participants()->attach($member);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('管理後台')
            ->assertSee('雪山東峰')
            ->assertSee('已報名人次');
    }

    public function test_non_admin_cannot_view_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
