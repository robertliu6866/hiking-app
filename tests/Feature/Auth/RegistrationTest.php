<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'phone' => '0912345678',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('lotteries.yushan', absolute: false));
        $response->assertSessionHas('status', 'member-created');
    }

    private function registration(array $overrides = []): array
    {
        return array_merge([
            'name' => '新山友', 'phone' => '0912345678', 'email' => 'hiker@example.com',
            'password' => 'hiking-password', 'password_confirmation' => 'hiking-password',
        ], $overrides);
    }

    public function test_registration_normalizes_contact_details_and_creates_trial_member(): void
    {
        $this->withSession(['registration-test' => true]);
        $previousSession = session()->getId();
        $this->post('/register', $this->registration([
            'name' => '  新山友  ', 'email' => '  HIKER@Example.COM  ', 'phone' => '+886 912-345-678',
        ]))->assertRedirect(route('lotteries.yushan', absolute: false));

        $user = User::where('email', 'hiker@example.com')->firstOrFail();
        $this->assertSame('0912345678', $user->phone);
        $this->assertSame('新山友', $user->name);
        $this->assertSame('trial', $user->membership_status);
        $this->assertEquals(2, $user->free_trial_quota);
        $this->assertTrue(Hash::check('hiking-password', $user->password));
        $this->assertNotSame($previousSession, session()->getId());
        $this->get(route('lotteries.yushan'))->assertOk()->assertSee('歡迎加入！')->assertSee('完善會員資料');
    }

    public function test_duplicate_contacts_are_rejected_after_normalization(): void
    {
        User::factory()->create(['email' => 'hiker@example.com', 'phone' => '0912345678']);
        $this->from('/register')->post('/register', $this->registration([
            'email' => 'HIKER@EXAMPLE.COM', 'phone' => '0912-345-678',
        ]))->assertRedirect('/register')->assertSessionHasErrors(['email', 'phone']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
        $this->get('/register')->assertOk()->assertSee('這個 Email 已註冊')->assertSee('重設密碼');
    }

    public function test_invalid_phone_and_password_do_not_create_an_account(): void
    {
        $this->post('/register', $this->registration([
            'phone' => '12345', 'password' => 'short', 'password_confirmation' => 'different',
        ]))->assertSessionHasErrors(['phone', 'password']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_returns_to_the_original_filtered_trip_page(): void
    {
        $target = route('lotteries.yushan', ['q' => '玉山', 'month' => '2026-11']);
        $this->get(route('register', ['redirect_to' => $target]))->assertOk();
        // Switching between login and registration keeps the intended destination.
        $this->get(route('login'))->assertOk();
        $this->post('/register', $this->registration())->assertRedirect($target);
    }

    public function test_auth_pages_reject_external_redirects_including_hostname_prefix_attacks(): void
    {
        foreach (['register', 'login'] as $route) {
            foreach ([url('/').'.evil.example/trips', '//evil.example', '/\\evil.example'] as $target) {
                $this->get(route($route, ['redirect_to' => $target]))->assertOk()
                    ->assertSessionMissing('url.intended');
            }
            $this->get(route($route, ['redirect_to' => '/lotteries/yushan?q=玉山']))
                ->assertOk()->assertSessionHas('url.intended', '/lotteries/yushan?q=玉山');
            session()->forget('url.intended');
        }
    }
}
