<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LineLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_with_line_and_link_their_line_identity(): void
    {
        config([
            'services.line_login.channel_id' => '123456',
            'services.line_login.channel_secret' => 'channel-secret',
            'services.line_login.callback_url' => 'https://liuliu.tw/auth/line/callback',
        ]);
        Http::fake([
            'https://api.line.me/oauth2/v2.1/token' => Http::response(['access_token' => 'user-token']),
            'https://api.line.me/v2/profile' => Http::response([
                'userId' => 'U-line-user',
                'displayName' => 'liuiu',
                'pictureUrl' => 'https://profile.example.com/liuiu.jpg',
            ]),
        ]);

        $this->withSession(['line_login_state' => 'valid-state'])
            ->get(route('login.line.callback', ['code' => 'auth-code', 'state' => 'valid-state']))
            ->assertRedirect(route('lotteries.yushan'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'liuiu',
            'line_user_id' => 'U-line-user',
            'line_display_name' => 'liuiu',
        ]);
    }

    public function test_line_login_rejects_an_invalid_state(): void
    {
        $this->withSession(['line_login_state' => 'expected-state'])
            ->get(route('login.line.callback', ['code' => 'auth-code', 'state' => 'wrong-state']))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
