<?php

namespace Tests\Feature;

use App\Livewire\WishJoinControl;
use App\Models\TripWish;
use App\Models\User;
use App\Support\WishCostSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WishDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_details_show_public_host_profile_fees_members_and_introduction(): void
    {
        $host = User::factory()->create(['name' => '測試主揪', 'hiking_experience' => '5-10年', 'bio' => '每個月都想上山走走']);
        $member = User::factory()->create(['name' => '同行山友']);
        $canceled = User::factory()->create(['name' => '已取消的山友']);
        $wish = TripWish::create([
            'user_id' => $host->id, 'mountain' => '雪山主峰', 'homepage_group' => 'guided',
            'guided_days' => 2, 'expected_participants' => 8, 'note' => '早上集合，兩天一起走。',
        ]);
        $wish->allUsers()->attach([$host->id => ['status' => 'joined'], $member->id => ['status' => 'joined'], $canceled->id => ['status' => 'canceled']]);

        Livewire::actingAs($member)->test(WishJoinControl::class, ['wishId' => $wish->id])
            ->assertDontSee('主揪資歷')
            ->call('openDetails')->assertSet('detailsOpen', true)
            ->assertSee('5-10年')->assertSee('每個月都想上山走走')
            ->assertSee('NT$500')->assertSee('NT$1,500')->assertSee('同行山友')
            ->assertSee('早上集合，兩天一起走。')->assertDontSee('已取消的山友')
            ->assertDontSee($host->email)->assertDontSee($member->email);
    }

    public function test_join_and_cancel_update_the_open_member_list(): void
    {
        $host = User::factory()->create();
        $member = User::factory()->create(['name' => '剛加入的山友']);
        $wish = TripWish::create(['user_id' => $host->id, 'mountain' => '玉山']);
        $component = Livewire::actingAs($member)->test(WishJoinControl::class, ['wishId' => $wish->id])
            ->call('openDetails')->assertSee('還沒有山友加入')
            ->call('toggle')->assertSet('detailsOpen', true)->assertSee('剛加入的山友')->assertSee('取消參加');
        $this->assertTrue($wish->users()->whereKey($member->id)->exists());
        $component->call('toggle')->assertSee('還沒有山友加入')->assertSee('我也想一起去');
        $this->assertFalse($wish->users()->whereKey($member->id)->exists());
    }

    public function test_fee_is_per_trip_and_missing_headcount_does_not_invent_a_total(): void
    {
        $host = User::factory()->create();
        $wish = TripWish::create(['user_id' => $host->id, 'mountain' => '玉山', 'guided_days' => 3, 'homepage_group' => 'guided', 'expected_participants' => 8]);
        $this->assertSame(500, WishCostSummary::for($wish)['guide']);
        $this->assertSame(1500, WishCostSummary::for($wish)['total']);
        $wish->update(['expected_participants' => null]);
        $this->assertNull(WishCostSummary::for($wish)['total']);
        Livewire::actingAs($host)->test(WishJoinControl::class, ['wishId' => $wish->id])->call('openDetails')
            ->assertSee('人數確認後計算')->assertSee('主揪尚未填寫')->assertSee('主揪還在準備行程介紹');
    }
}
