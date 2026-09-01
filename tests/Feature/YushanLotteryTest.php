<?php

namespace Tests\Feature;

use App\Livewire\HomepageWishButton;
use App\Livewire\HomepageWeekendPanel;
use App\Livewire\WishJoinControl;
use App\Models\Trip;
use App\Models\TripWish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class YushanLotteryTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guest_homepage_shows_login_and_register_entry(): void
    {
        $user = User::factory()->create(['name' => '許願山友']);

        TripWish::create([
            'user_id' => $user->id,
            'mountain' => '雪山主峰',
            'wished_date' => now()->addWeek(),
            'note' => '想找週末同行的山友',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('許願活動')
            ->assertSee('雪山主峰')
            ->assertSee('想找週末同行的山友')
            ->assertSee('想去的山友')
            ->assertSee('許願山友')
            ->assertSee('登入')
            ->assertSee('加入會員，許願 +1');
    }

    public function test_authenticated_homepage_redirects_to_wish_board(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('lotteries.yushan'));
    }

    public function test_login_is_required_for_wish_board(): void
    {
        $this->get(route('lotteries.yushan'))
            ->assertRedirect(route('login'));
    }

    public function test_homepage_weekend_panel_switches_peak_without_page_reload(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-20 08:00:00'));

        $user = User::factory()->create();

        TripWish::create([
            'user_id' => $user->id,
            'mountain' => '玉山',
            'wished_date' => '2026-05-23',
            'note' => '玉山週末想去',
        ]);

        TripWish::create([
            'user_id' => $user->id,
            'mountain' => '雪山',
            'wished_date' => '2026-05-23',
            'note' => '雪山週末想去',
        ]);

        Livewire::test(HomepageWeekendPanel::class, [
            'selectedPeak' => '玉山',
            'dateKey' => '2026-05-23',
            'weekdayLabel' => '六',
            'displayDate' => '05/23',
            'guideThreshold' => 20,
        ])
            ->assertSee('05/23 玉山')
            ->assertSee('玉山週末想去')
            ->assertDontSee('雪山週末想去')
            ->call('selectPeak', '雪山')
            ->assertSee('05/23 雪山')
            ->assertSee('雪山週末想去')
            ->assertDontSee('玉山週末想去');
    }

    public function test_user_can_open_yushan_lottery_page(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-09 08:00:00'));

        $user = User::factory()->create();
        TripWish::create([
            'user_id' => $user->id,
            'mountain' => '雪山主峰',
            'wished_date' => '2026-07-14',
            'note' => '符合顯示條件',
        ]);
        TripWish::create([
            'user_id' => $user->id,
            'mountain' => '玉山主峰',
            'wished_date' => '2026-07-13',
            'note' => '太近不顯示',
        ]);

        $this->actingAs($user)
            ->get(route('lotteries.yushan'))
            ->assertOk()
            ->assertSee('找同樣想上山的人')
            ->assertSee('發起許願')
            ->assertSee('發布許願')
            ->assertSee('大家正在許願')
            ->assertSee('雪山主峰')
            ->assertDontSee('玉山主峰')
            ->assertSee('2026/07/14');
    }

    public function test_user_can_create_wish_from_top_form_with_route_mode_and_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-09 08:00:00'));

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('trip-wishes.store'), [
                'mountain' => '雪山主峰',
                'wished_date' => '2026-07-16',
                'route_mode' => 'single',
                'note' => '想找八月週末一起上山',
                'redirect_to' => route('lotteries.yushan'),
            ])
            ->assertRedirect(route('lotteries.yushan'));

        $this->assertDatabaseHas('trip_wishes', [
            'mountain' => '雪山主峰',
            'wished_date' => '2026-07-16 00:00:00',
            'route_mode' => 'single',
            'note' => '想找八月週末一起上山',
        ]);
    }

    public function test_user_can_create_a_guided_wish_with_shared_cost_inputs(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-09 08:00:00'));

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('trip-wishes.store'), [
                'mountain' => '南湖大山',
                'wished_date' => '2026-07-16',
                'homepage_group' => 'guided',
                'guided_days' => 2,
                'expected_participants' => 8,
                'redirect_to' => route('lotteries.yushan'),
            ])
            ->assertRedirect(route('lotteries.yushan'));

        $this->assertDatabaseHas('trip_wishes', [
            'mountain' => '南湖大山',
            'homepage_group' => 'guided',
            'guided_days' => 2,
            'expected_participants' => 8,
        ]);

        $this->actingAs($user)
            ->get(route('lotteries.yushan'))
            ->assertSee('請嚮導帶團')
            ->assertSee('揪團人數')
            ->assertSee('每人預估費用')
            ->assertSee('NT$2,000');
    }

    public function test_self_organized_single_attack_shows_car_share_and_host_is_free(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-09 08:00:00'));
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('trip-wishes.store'), [
                'mountain' => '合歡北峰',
                'wished_date' => '2026-07-16',
                'route_mode' => 'single',
                'homepage_group' => 'self',
                'expected_participants' => 5,
                'redirect_to' => route('lotteries.yushan'),
            ])
            ->assertRedirect(route('lotteries.yushan'));

        $this->actingAs($user)
            ->get(route('lotteries.yushan'))
            ->assertSee('每位同行者預估')
            ->assertSee('NT$2,000')
            ->assertSee('主揪免車資');
    }

    public function test_yushan_lottery_page_orders_by_date_and_paginates_after_five_items(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-09 08:00:00'));

        $user = User::factory()->create();

        foreach (range(0, 5) as $offset) {
            TripWish::create([
                'user_id' => $user->id,
                'mountain' => '測試山岳'.($offset + 1),
                'wished_date' => Carbon::parse('2026-07-14')->addDays($offset)->toDateString(),
                'note' => '分頁測試',
            ]);
        }

        $firstPage = $this->actingAs($user)
            ->get(route('lotteries.yushan'));

        $firstPage
            ->assertOk()
            ->assertSee('測試山岳1')
            ->assertSee('測試山岳5')
            ->assertDontSee('測試山岳6')
            ->assertSee('?page=2', false);

        $secondPage = $this->actingAs($user)
            ->get(route('lotteries.yushan', ['page' => 2]));

        $secondPage
            ->assertOk()
            ->assertSee('測試山岳6')
            ->assertDontSee('測試山岳1');
    }

    public function test_user_can_plus_one_yushan_lottery(): void
    {
        $user = User::factory()->create();
        $wish = TripWish::create([
            'user_id' => $user->id,
            'mountain' => '玉山抽籤',
            'note' => '按 +1 參加玉山抽籤意願名單，成團前會依人數與日期協調。',
        ]);

        $this->actingAs($user);

        Livewire::test(WishJoinControl::class, [
            'wishId' => $wish->id,
            'allowCancel' => true,
            'showCreateTripLink' => false,
            'simpleJoinLabel' => true,
        ])->call('toggle');

        $this->assertTrue($wish->users()->whereKey($user->id)->exists());
    }

    public function test_yushan_lottery_plus_one_can_be_canceled_from_lottery_page(): void
    {
        $user = User::factory()->create();
        $wish = TripWish::create([
            'user_id' => $user->id,
            'mountain' => '玉山抽籤',
            'note' => '按 +1 參加玉山抽籤意願名單，成團前會依人數與日期協調。',
        ]);
        $wish->users()->attach($user->id);

        $this->actingAs($user);

        Livewire::test(WishJoinControl::class, [
            'wishId' => $wish->id,
            'allowCancel' => true,
            'showCreateTripLink' => false,
            'simpleJoinLabel' => true,
        ])->call('toggle');

        $this->assertFalse($wish->users()->whereKey($user->id)->exists());
    }

    public function test_homepage_wish_avatars_follow_join_status(): void
    {
        $user = User::factory()->create(['name' => '取消測試山友']);
        $wish = TripWish::create([
            'user_id' => $user->id,
            'mountain' => '玉山',
            'wished_date' => now()->addMonth()->toDateString(),
            'note' => '首頁頭像測試',
        ]);

        $wish->allUsers()->attach($user->id, ['status' => 'joined']);

        $this->actingAs($user);

        Livewire::test(HomepageWishButton::class, [
            'wishId' => $wish->id,
            'mountain' => $wish->mountain,
            'wishedDate' => $wish->wished_date->toDateString(),
            'note' => $wish->note,
            'variant' => 'dark',
            'showAvatars' => true,
        ])
            ->assertSee('取消測試山友')
            ->call('toggle')
            ->assertDontSee('取消測試山友');

        $this->assertDatabaseHas('trip_wish_user', [
            'trip_wish_id' => $wish->id,
            'user_id' => $user->id,
            'status' => 'canceled',
        ]);
    }

    public function test_homepage_weekend_panel_can_join_and_cancel_without_nested_refresh(): void
    {
        $user = User::factory()->create(['name' => '首頁取消山友']);

        $this->actingAs($user);

        Livewire::test(HomepageWeekendPanel::class, [
            'selectedPeak' => '玉山',
            'dateKey' => '2026-05-23',
            'weekdayLabel' => '六',
            'displayDate' => '05/23',
            'guideThreshold' => 20,
        ])
            ->assertSee('+1')
            ->call('toggleWish')
            ->assertSee('取消報名')
            ->assertSee('首頁取消山友')
            ->call('toggleWish')
            ->assertSee('+1')
            ->assertDontSee('首頁取消山友');

        $wish = TripWish::where('mountain', '玉山')
            ->whereDate('wished_date', '2026-05-23')
            ->firstOrFail();

        $this->assertDatabaseHas('trip_wish_user', [
            'trip_wish_id' => $wish->id,
            'user_id' => $user->id,
            'status' => 'canceled',
        ]);
    }

    public function test_homepage_weekend_guide_groups_keep_join_buttons_independent(): void
    {
        $user = User::factory()->create(['name' => '分組測試山友']);
        $wish = TripWish::create([
            'user_id' => $user->id,
            'mountain' => '玉山',
            'wished_date' => '2026-05-23',
            'note' => '沒有嚮導分組',
        ]);

        $wish->allUsers()->attach($user->id, ['status' => 'joined']);

        $this->actingAs($user);

        $component = Livewire::test(HomepageWeekendPanel::class, [
            'selectedPeak' => '玉山',
            'dateKey' => '2026-05-23',
            'weekdayLabel' => '六',
            'displayDate' => '05/23',
            'guideThreshold' => 20,
        ]);

        $this->assertSame(1, substr_count($component->html(), '<span x-show="! busy" x-text="buttonLabel()">取消報名</span>'));
        $component->assertSee('想跟團 · 0 人 +1');
        $component->assertSee('沒有嚮導分組');
    }

    public function test_homepage_empty_guide_group_plus_one_stays_in_guide_group(): void
    {
        $user = User::factory()->create(['name' => '有嚮導按鈕山友']);

        $this->actingAs($user);

        Livewire::test(HomepageWeekendPanel::class, [
            'selectedPeak' => '玉山',
            'dateKey' => '2026-05-23',
            'weekdayLabel' => '六',
            'displayDate' => '05/23',
            'guideThreshold' => 20,
        ])
            ->assertSee('想跟團 · 0 人 +1')
            ->call('createWish', 'guided')
            ->assertSee('有嚮導按鈕山友')
            ->assertSee('取消報名');

        $this->assertDatabaseHas('trip_wishes', [
            'mountain' => '玉山',
            'wished_date' => '2026-05-23 00:00:00',
            'homepage_group' => 'guided',
        ]);
    }

    public function test_homepage_guest_plus_one_login_keeps_intended_homepage_date(): void
    {
        $redirectTo = url('/').'?peak='.urlencode('玉山').'&date=2026-05-23';

        $this->get(route('login', ['redirect_to' => $redirectTo]))
            ->assertOk();

        $this->assertSame($redirectTo, session('url.intended'));
    }

    public function test_homepage_standard_plus_one_post_returns_to_selected_date(): void
    {
        $user = User::factory()->create(['name' => '表單山友']);
        $redirectTo = url('/').'?peak='.urlencode('玉山').'&date=2026-05-23';

        $this->actingAs($user)
            ->post(route('trip-wishes.store'), [
                'mountain' => '玉山',
                'wished_date' => '2026-05-23',
                'note' => '首頁週末許願',
                'redirect_to' => $redirectTo,
            ])
            ->assertRedirect($redirectTo);

        $wish = TripWish::where('mountain', '玉山')
            ->whereDate('wished_date', '2026-05-23')
            ->firstOrFail();

        $this->assertTrue($wish->users()->whereKey($user->id)->exists());
    }
}
