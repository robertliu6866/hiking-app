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
            ->assertSee('我們怎麼玩？')
            ->assertSee('不是找主揪，是找到願意同行的人。')
            ->assertSee('第一位許願的人不必帶隊')
            ->assertSee('我懂了，開始找山友');
    }

    public function test_signed_in_hiker_also_sees_the_welcome_dialog_when_returning(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('lotteries.yushan'))
            ->assertOk()
            ->assertSee('我們怎麼玩？');
    }
}
