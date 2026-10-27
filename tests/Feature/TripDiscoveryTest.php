<?php

namespace Tests\Feature;

use App\Models\TripWish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TripDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-24 12:00:00');
        $this->actingAs(User::factory()->create());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function wish(array $attributes = []): TripWish
    {
        return TripWish::create(array_merge([
            'user_id' => auth()->id(), 'mountain' => '玉山主峰',
            'wished_date' => '2026-11-07', 'homepage_group' => 'self',
            'route_mode' => 'custom', 'guided_days' => 2,
        ], $attributes));
    }

    public function test_filters_combine_and_totals_only_include_matches(): void
    {
        $match = $this->wish();
        $this->wish(['guided_days' => 1, 'route_mode' => 'single']);
        $this->wish(['homepage_group' => 'guided']);
        $this->wish(['wished_date' => '2026-12-05']);
        $this->wish(['mountain' => '雪山主峰']);
        $this->wish(['wished_date' => '2026-10-25']);
        $this->get(route('lotteries.yushan', [
            'q' => '玉山', 'duration' => 'overnight', 'month' => '2026-11', 'group' => 'self',
        ]))->assertOk()->assertViewHas('wishes', fn ($wishes) => $wishes->total() === 1 && $wishes->first()->id === $match->id)
            ->assertViewHas('totalMountains', 1)->assertSee('兩天一夜');
    }

    public function test_single_day_filter_includes_legacy_records_and_excludes_multiday_records(): void
    {
        $match = $this->wish(['guided_days' => null, 'route_mode' => 'single']);
        $this->wish(['guided_days' => 2, 'route_mode' => 'single']);
        $this->wish();
        $this->get(route('lotteries.yushan', ['duration' => 'single']))
            ->assertOk()->assertViewHas('wishes', fn ($wishes) => $wishes->total() === 1 && $wishes->first()->id === $match->id);
    }

    public function test_search_finds_hosts_and_treats_wildcards_as_literal_text(): void
    {
        $host = User::factory()->create(['name' => '山友 100%']);
        $match = $this->wish(['user_id' => $host->id]);
        $this->wish(['mountain' => '雪山主峰']);
        foreach (['100%', '山友 100'] as $query) {
            $this->get(route('lotteries.yushan', ['q' => $query]))
                ->assertOk()->assertViewHas('wishes', fn ($wishes) => $wishes->total() === 1 && $wishes->first()->id === $match->id);
        }
    }

    public function test_popular_sort_and_pagination_preserve_search_conditions(): void
    {
        foreach (range(1, 6) as $day) {
            $this->wish(['wished_date' => '2026-11-'.str_pad($day, 2, '0', STR_PAD_LEFT)]);
        }
        $popular = $this->wish(['wished_date' => '2026-11-28']);
        $popular->users()->attach(auth()->id(), ['status' => 'joined']);
        $params = ['q' => '玉山', 'month' => '2026-11', 'duration' => 'overnight', 'sort' => 'popular'];
        $this->get(route('lotteries.yushan', $params))->assertOk()
            ->assertViewHas('wishes', fn ($wishes) => $wishes->first()->id === $popular->id && $wishes->total() === 7)
            ->assertSee('duration=overnight')->assertSee('month=2026-11');
        $this->get(route('lotteries.yushan', $params + ['page' => 2]))
            ->assertOk()->assertViewHas('wishes', fn ($wishes) => $wishes->count() === 2 && $wishes->currentPage() === 2);
    }

    public function test_empty_results_offer_reset_and_invalid_filter_is_rejected(): void
    {
        $this->get(route('lotteries.yushan', ['month' => '2027-02']))
            ->assertOk()->assertSee('目前沒有符合條件的行程')->assertSee('查看全部行程');
        $this->getJson(route('lotteries.yushan', ['month' => '2026-99', 'duration' => 'invalid']))
            ->assertUnprocessable()->assertJsonValidationErrors(['month', 'duration']);
    }

    public function test_guest_search_keeps_conditions_after_login_redirect(): void
    {
        auth()->logout();
        $this->get('/')->assertOk()->assertSee('找符合需求的行程')->assertSee('找過夜團');
        $target = route('lotteries.yushan', ['q' => '玉山', 'duration' => 'overnight']);
        $this->get($target)->assertRedirect(route('login'));
        $this->assertSame(parse_url($target, PHP_URL_PATH), parse_url(session('url.intended'), PHP_URL_PATH));
        parse_str(parse_url(session('url.intended'), PHP_URL_QUERY), $query);
        $this->assertEquals(['q' => '玉山', 'duration' => 'overnight'], $query);
    }

    public function test_instant_search_returns_matching_interactive_cards_and_filtered_pagination(): void
    {
        foreach (range(1, 6) as $day) {
            $this->wish(['wished_date' => '2026-11-'.str_pad($day, 2, '0', STR_PAD_LEFT)]);
        }
        $this->wish(['mountain' => '雪山主峰']);

        $params = ['q' => '玉山', 'month' => '2026-11'];
        $this->withHeader('X-Trip-Search', '1')->get(route('lotteries.yushan', $params))
            ->assertOk()->assertViewIs('lotteries.partials.results')
            ->assertSee('玉山主峰')->assertDontSee('雪山主峰')
            ->assertSee('wire:snapshot', false)->assertSee('rel="next"', false)
            ->assertSee('month=2026-11')->assertDontSee('<html', false);

        $this->withHeader('X-Trip-Search', '1')->get(route('lotteries.yushan', $params + ['page' => 2]))
            ->assertOk()->assertViewHas('wishes', fn ($wishes) => $wishes->count() === 1 && $wishes->currentPage() === 2)
            ->assertSee('rel="prev"', false);

        $this->withHeader('X-Trip-Search', '1')->get(route('lotteries.yushan', ['q' => '找不到的山']))
            ->assertOk()->assertSee('目前沒有符合條件的行程');
    }
}
