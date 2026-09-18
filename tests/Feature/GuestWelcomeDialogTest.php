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
            ->assertSee('喜歡爬山，所以一直回來')
            ->assertSee('只因為爬山帶來的平靜，以及下山後，那種重新充滿活力的感覺。')
            ->assertSee('當你這週想上山，就能找到適合一起出發的夥伴。')
            ->assertSee('找到對的夥伴，方便出門。')
            ->assertSee('快樂上山，安全下山。')
            ->assertSee('我懂了，開始找山友');
    }

    public function test_signed_in_hiker_also_sees_the_welcome_dialog_when_returning(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('lotteries.yushan'))
            ->assertOk()
            ->assertSee('喜歡爬山，所以一直回來');
    }
}
