<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile/show');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_hiking_preferences_can_be_saved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\ProfileEditForm::class, ['user' => $user])
            ->set('preferred_regions', ['北部', '中部'])
            ->set('available_days', ['weekend'])
            ->set('transport_modes', ['carpool', 'public_transport'])
            ->set('preferred_route_modes', ['single'])
            ->set('hiking_styles', ['slow', 'photo'])
            ->set('preferred_difficulty_min', 2)
            ->set('preferred_difficulty_max', 4)
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame(['北部', '中部'], $user->preferred_regions);
        $this->assertSame(['weekend'], $user->available_days);
        $this->assertSame(['carpool', 'public_transport'], $user->transport_modes);
        $this->assertSame(['single'], $user->preferred_route_modes);
        $this->assertSame(['slow', 'photo'], $user->hiking_styles);
        $this->assertSame(2, $user->preferred_difficulty_min);
        $this->assertSame(4, $user->preferred_difficulty_max);
    }

    public function test_hiking_preference_buttons_toggle_values(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\ProfileEditForm::class, ['user' => $user])
            ->call('togglePreference', 'preferred_regions', '北部')
            ->assertSet('preferred_regions', ['北部'])
            ->call('togglePreference', 'preferred_regions', '北部')
            ->assertSet('preferred_regions', [])
            ->call('togglePreference', 'transport_modes', 'carpool')
            ->assertSet('transport_modes', ['carpool']);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile/show');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
