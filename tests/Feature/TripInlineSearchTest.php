<?php

namespace Tests\Feature;

use App\Livewire\TripInlineSearch;
use App\Models\TripWish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class TripInlineSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-28 12:00:00');
        $this->actingAs(User::factory()->create(['name' => '山友小林']));
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
            'wished_date' => '2026-11-07', 'route_mode' => 'single',
            'homepage_group' => 'self', 'note' => '慢慢走，一起看星空',
        ], $attributes));
    }

    public function test_search_requires_two_characters_and_finds_mountain_host_and_note(): void
    {
        $this->wish();
        $this->wish(['mountain' => '雪山主峰', 'wished_date' => '2026-10-29']);
        $component = Livewire::test(TripInlineSearch::class)
            ->assertSee('輸入至少 2 個字')->set('search', '玉')->assertDontSee('玉山主峰');
        foreach (['玉山', '小林', '星空'] as $term) {
            $component->set('search', $term)->assertSee('玉山')->assertDontSee('雪山');
            $this->assertCount(1, $component->get('results'));
        }
        $component->set('search', str_repeat('山', 101));
        $this->assertCount(0, $component->get('results'));
    }

    public function test_search_respects_filters_and_limits_results(): void
    {
        foreach (range(1, 12) as $number) {
            $this->wish(['mountain' => '玉山路線'.$number]);
        }
        $match = $this->wish(['mountain' => '玉山過夜', 'guided_days' => 2, 'homepage_group' => 'guided']);
        $this->wish(['mountain' => '玉山十二月', 'wished_date' => '2026-12-01', 'guided_days' => 2, 'homepage_group' => 'guided']);
        $component = Livewire::test(TripInlineSearch::class, ['search' => '玉山']);
        $this->assertCount(10, $component->get('results'));

        $filtered = Livewire::test(TripInlineSearch::class, [
            'search' => '玉山', 'filters' => ['duration' => 'overnight', 'month' => '2026-11', 'group' => 'guided'],
        ]);
        $this->assertSame([$match->id], $filtered->get('results')->modelKeys());
        $filtered->call('updatePreferences', 'self', 'date');
        $this->assertCount(0, $filtered->get('results'));
    }

    public function test_wildcards_are_literal_and_highlighting_escapes_html(): void
    {
        $this->wish(['mountain' => '100% 山路 <script>alert(1)</script>']);
        $this->wish(['mountain' => '其他山路']);
        $component = Livewire::test(TripInlineSearch::class, ['search' => '100%']);
        $this->assertCount(1, $component->get('results'));
        $component->assertSeeHtml('<mark class="rounded bg-amber-100 px-0.5 text-inherit">100%</mark>')
            ->assertDontSeeHtml('<script>alert(1)</script>');
        $html = $component->instance()->highlight('100% <script>alert(1)</script>');
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_selecting_result_remembers_search_and_opens_selected_trip(): void
    {
        $wish = $this->wish();
        Livewire::test(TripInlineSearch::class, ['search' => '玉山'])
            ->call('choose', $wish->id)->assertRedirect(route('lotteries.yushan', ['wish' => $wish->id]));
        Livewire::test(TripInlineSearch::class)->assertSet('recentSearches', ['玉山'])
            ->call('useRecent', '玉山')->assertSet('search', '玉山');
        $this->get(route('lotteries.yushan', ['wish' => $wish->id]))->assertOk()
            ->assertViewHas('selectedWishId', $wish->id)->assertSeeHtml('&quot;detailsOpen&quot;:true');
        Livewire::test(TripInlineSearch::class)->call('clearRecent')->assertSet('recentSearches', []);
        Livewire::test(TripInlineSearch::class)->assertSet('recentSearches', []);
    }

    public function test_recent_searches_are_unique_and_keep_only_last_five(): void
    {
        $wish = $this->wish(['note' => 'Alpha Bravo Charlie Delta Echo Foxtrot']);
        foreach (['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo', 'Foxtrot', 'ALPHA'] as $term) {
            Livewire::test(TripInlineSearch::class, ['search' => $term])->call('choose', $wish->id);
        }
        Livewire::test(TripInlineSearch::class)
            ->assertSet('recentSearches', ['ALPHA', 'Foxtrot', 'Echo', 'Delta', 'Charlie']);
    }

    public function test_unmatched_result_cannot_be_selected(): void
    {
        $wish = $this->wish();
        Livewire::test(TripInlineSearch::class, ['search' => '雪山'])
            ->call('choose', $wish->id)->assertNotFound();
    }
}
