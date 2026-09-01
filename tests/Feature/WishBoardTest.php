<?php

namespace Tests\Feature;

use App\Livewire\WishJoinControl;
use App\Models\TripWish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WishBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_join_and_cancel_a_wish_without_page_reload(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $wish = TripWish::create([
            'user_id' => $owner->id,
            'mountain' => '羊頭山',
            'wished_date' => now()->addWeek()->toDateString(),
        ]);

        $this->actingAs($user);

        Livewire::test(WishJoinControl::class, ['wishId' => $wish->id])
            ->call('toggle');

        $this->assertTrue($wish->users()->whereKey($user->id)->exists());

        Livewire::test(WishJoinControl::class, ['wishId' => $wish->id])
            ->call('toggle');

        $this->assertFalse($wish->users()->whereKey($user->id)->exists());
    }

    public function test_dashboard_shows_wishes_the_user_has_joined(): void
    {
        $owner = User::factory()->create(['name' => '發起山友']);
        $user = User::factory()->create(['name' => '自己']);
        $wish = TripWish::create([
            'user_id' => $owner->id,
            'mountain' => '雪山',
            'wished_date' => now()->addWeek()->toDateString(),
            'note' => '下週想找人一起雪山單攻',
        ]);
        $wish->allUsers()->attach($user->id, ['status' => 'joined']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('我的許願')
            ->assertSee('雪山')
            ->assertSee('下週想找人一起雪山單攻');
    }

    public function test_wish_owner_can_draw_a_host_only_from_volunteers(): void
    {
        $owner = User::factory()->create();
        $volunteer = User::factory()->create(['name' => '自願山友']);
        $participant = User::factory()->create();
        $wish = TripWish::create([
            'user_id' => $owner->id,
            'mountain' => '北大武山',
            'wished_date' => now()->addWeek()->toDateString(),
        ]);
        $wish->allUsers()->attach($volunteer->id, ['status' => 'joined', 'willing_to_host' => true]);
        $wish->allUsers()->attach($participant->id, ['status' => 'joined', 'willing_to_host' => false]);

        $this->actingAs($owner);

        Livewire::test(WishJoinControl::class, ['wishId' => $wish->id])
            ->call('drawHost');

        $this->assertSame($volunteer->id, $wish->fresh()->host_user_id);
    }
}
