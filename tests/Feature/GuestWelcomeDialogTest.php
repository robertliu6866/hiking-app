<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestWelcomeDialogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_homepage_contains_the_first_visit_welcome_dialog(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('我們為什麼成立？')
            ->assertSee('陌生揪團最大的問題，不是找不到人，而是找不到可以長期一起走的人。')
            ->assertSee('實名制｜信用累積｜公平分工｜品質山友')
            ->assertSee('不是找人共乘，而是找到值得長期一起登山的人。')
            ->assertSee('我懂了，開始找山友');
    }

    public function test_signed_in_hiker_also_sees_the_welcome_dialog_when_returning(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('lotteries.yushan'))
            ->assertOk()
            ->assertSee('我們為什麼成立？');
    }
}
