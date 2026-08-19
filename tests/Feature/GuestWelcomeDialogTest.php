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
            ->assertSee('認同，山上見');
    }

    public function test_signed_in_hiker_does_not_receive_the_guest_welcome_dialog(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('lotteries.yushan'))
            ->assertOk()
            ->assertDontSee('我們怎麼玩？');
    }
}
