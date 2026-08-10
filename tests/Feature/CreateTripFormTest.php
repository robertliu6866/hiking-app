<?php

namespace Tests\Feature;

use App\Livewire\CreateTripForm;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateTripFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_trip_page_can_be_rendered(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->get(route('trips.create'))
            ->assertOk()
            ->assertSee('建立活動')
            ->assertSee('基本')
            ->assertSee('路線')
            ->assertSee('安全');
    }

    public function test_user_can_create_trip_with_safety_details(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user);

        $component = Livewire::test(CreateTripForm::class)
            ->set('title', '合歡北峰日出單攻')
            ->set('mountain', '合歡山北峰')
            ->set('category', '百岳')
            ->set('route_mode', 'single')
            ->set('difficulty', 2)
            ->set('distance_km', '5.8')
            ->set('elevation_gain_m', '520')
            ->set('estimated_hours', '4.5')
            ->set('location', '南投')
            ->set('departure_time', now()->addWeek()->format('Y-m-d\TH:i'))
            ->set('meeting_point', '小風口停車場')
            ->set('price', 0)
            ->set('quota', 6)
            ->set('trailhead', '小風口')
            ->set('summit', '合歡山北峰')
            ->set('turnaround_time', '11:00')
            ->set('suitable_for', '有郊山經驗、可行走 5 小時')
            ->set('equipment', "雨衣\n頭燈\n保暖層")
            ->set('safety_note', '若 11:00 未抵達稜線即折返。')
            ->set('cancellation_policy', '豪雨特報或颱風警報即取消。')
            ->set('description', '慢速上行，看天候調整節奏。')
            ->call('save')
            ->assertHasNoErrors();

        $trip = Trip::firstOrFail();
        $component->assertRedirect(route('trips.show', $trip));

        $this->assertSame($user->id, $trip->user_id);
        $this->assertSame('合歡北峰日出單攻', $trip->title);
        $this->assertSame('小風口', $trip->route_details['trailhead']);
        $this->assertSame(['雨衣', '頭燈', '保暖層'], $trip->route_details['equipment']);
        $this->assertSame('有郊山經驗、可行走 5 小時', $trip->route_details['suitable_for']);
        $this->assertSame('豪雨特報或颱風警報即取消。', $trip->route_details['cancellation_policy']);
    }

    public function test_create_trip_form_can_be_prefilled_from_a_wish(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $wishedDate = now()->addMonth()->toDateString();

        $this->actingAs($user)
            ->get(route('trips.create', [
                'mountain' => '南湖大山',
                'wished_date' => $wishedDate,
                'note' => '希望平日出發',
            ]))
            ->assertOk();

        Livewire::test(CreateTripForm::class, [
            'prefill' => [
                'mountain' => '南湖大山',
                'wished_date' => $wishedDate,
                'route_mode' => 'traverse',
                'note' => '希望平日出發',
            ],
        ])
            ->assertSet('title', '南湖大山開團')
            ->assertSet('mountain', '南湖大山')
            ->assertSet('summit', '南湖大山')
            ->assertSet('departure_time', $wishedDate.'T06:00')
            ->assertSet('route_mode', 'traverse')
            ->assertSet('description', '希望平日出發');
    }

    public function test_user_can_switch_route_modes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateTripForm::class)
            ->assertSet('route_mode', 'single')
            ->call('setRouteMode', 'traverse')
            ->assertSet('route_mode', 'traverse')
            ->assertSee('縱走資訊')
            ->call('setRouteMode', 'custom')
            ->assertSet('route_mode', 'custom')
            ->assertSee('自由規劃備註')
            ->call('setRouteMode', 'invalid')
            ->assertSet('route_mode', 'custom');
    }

    public function test_user_can_create_trip_with_standard_form_post(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)
            ->post(route('trips.store'), [
                'title' => '南湖群峰縱走',
                'mountain' => '南湖大山',
                'category' => '百岳',
                'route_mode' => 'traverse',
                'difficulty' => 4,
                'distance_km' => '38.5',
                'elevation_gain_m' => '2800',
                'estimated_hours' => '22',
                'location' => '宜蘭',
                'departure_time' => now()->addWeeks(2)->format('Y-m-d\TH:i'),
                'meeting_point' => '勝光登山口',
                'price' => 0,
                'quota' => 5,
                'start_point' => '勝光登山口',
                'end_point' => '勝光登山口',
                'waypoints' => "雲稜山莊\n南湖圈谷",
                'suitable_for' => '有高山過夜經驗',
                'equipment' => "睡袋\n頭燈",
                'safety_note' => '午後天候不穩即撤退',
                'cancellation_policy' => '颱風警報取消',
                'description' => '三天縱走測試',
            ]);

        $trip = Trip::firstOrFail();

        $response
            ->assertRedirect(route('trips.show', $trip))
            ->assertSessionHas('status', 'trip-created');

        $this->assertSame($user->id, $trip->user_id);
        $this->assertSame('南湖群峰縱走', $trip->title);
        $this->assertSame('traverse', $trip->route_mode);
        $this->assertSame(['雲稜山莊', '南湖圈谷'], $trip->route_details['waypoints']);
    }
}
