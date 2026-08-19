<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_hiker_sees_the_how_we_play_dialog(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('lotteries.yushan'))
            ->assertOk()
            ->assertSee('我們怎麼玩？')
            ->assertSee('認同，山上見');
    }

    public function test_existing_hiker_does_not_see_the_dialog_after_acknowledging_it(): void
    {
        $user = User::factory()->create(['onboarding_seen_at' => now()]);

        $this->actingAs($user)
            ->get(route('lotteries.yushan'))
            ->assertOk()
            ->assertDontSee('我們怎麼玩？');
    }

    public function test_hiker_can_acknowledge_the_dialog_once(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('onboarding.complete'))
            ->assertNoContent();

        $this->assertNotNull($user->fresh()->onboarding_seen_at);
    }
}
