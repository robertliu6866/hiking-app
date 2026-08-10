<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\TripOrder;
use App\Models\User;
use App\Notifications\BankTransferSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use App\Livewire\TripJoinControl;
use Tests\TestCase;

class TripPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_trip_join_uses_simple_registration_flow(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $trip = $this->paidTrip($host);

        $this->actingAs($user)
            ->post(route('trips.join', $trip))
            ->assertRedirect()
            ->assertSessionHas('status', 'trip-joined');

        $this->assertSame(0, TripOrder::count());
        $this->assertTrue($trip->participants()->whereKey($user->id)->exists());
    }

    public function test_bank_transfer_submission_waits_for_host_confirmation(): void
    {
        Notification::fake();

        $host = User::factory()->create();
        $user = User::factory()->create();
        $trip = $this->paidTrip($host);
        $order = TripOrder::create([
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'merchant_order_id' => 'TRIP-TEST-1',
            'amount' => $trip->price,
        ]);

        $this->actingAs($user)
            ->post(route('payments.bank-transfer', $order), [
                'bank_transfer_name' => '王小明',
                'bank_transfer_last_five' => '12345',
            ])
            ->assertRedirect(route('payments.show', $order));

        $order->refresh();

        $this->assertSame('bank_transfer', $order->payment_method);
        $this->assertSame('bank_transfer_pending', $order->status);
        $this->assertFalse($trip->participants()->whereKey($user->id)->exists());

        Notification::assertSentTo($host, BankTransferSubmitted::class);
    }

    public function test_pending_bank_transfer_is_shown_as_provisional_member(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create(['name' => '準團員山友']);
        $trip = $this->paidTrip($host);
        TripOrder::create([
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'merchant_order_id' => 'TRIP-TEST-3',
            'amount' => $trip->price,
            'payment_method' => 'bank_transfer',
            'status' => 'bank_transfer_pending',
            'bank_transfer_name' => '王小明',
            'bank_transfer_last_five' => '12345',
        ]);

        $this->actingAs($host);

        Livewire::test(TripJoinControl::class, [
            'tripId' => $trip->id,
            'variant' => 'detail',
        ])
            ->assertSee('準團員')
            ->assertSee('準團員山友')
            ->assertSee('已送出匯款通知')
            ->assertSee('1 人待確認');
    }

    public function test_simple_registration_respects_joined_participant_quota(): void
    {
        $host = User::factory()->create();
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $trip = $this->paidTrip($host);
        $trip->update(['quota' => 1]);

        $trip->participants()->attach($firstUser->id);

        $this->actingAs($secondUser)
            ->post(route('trips.join', $trip))
            ->assertSessionHasErrors('trip');

        $this->assertFalse($trip->participants()->whereKey($secondUser->id)->exists());
    }

    public function test_host_can_confirm_bank_transfer_and_join_user(): void
    {
        $host = User::factory()->create();
        $user = User::factory()->create();
        $trip = $this->paidTrip($host);
        $order = TripOrder::create([
            'trip_id' => $trip->id,
            'user_id' => $user->id,
            'merchant_order_id' => 'TRIP-TEST-2',
            'amount' => $trip->price,
            'payment_method' => 'bank_transfer',
            'status' => 'bank_transfer_pending',
            'bank_transfer_name' => '王小明',
            'bank_transfer_last_five' => '12345',
        ]);

        $this->actingAs($host)
            ->post(route('payments.bank-transfer.confirm', $order))
            ->assertRedirect(route('payments.show', $order));

        $order->refresh();

        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertTrue($trip->participants()->whereKey($user->id)->exists());
    }

    private function paidTrip(User $host): Trip
    {
        return Trip::create([
            'user_id' => $host->id,
            'title' => '合歡北峰日出團',
            'mountain' => '合歡北峰',
            'category' => '百岳',
            'location' => '南投',
            'departure_time' => now()->addWeek(),
            'price' => 1200,
            'quota' => 6,
            'status' => 'open',
        ]);
    }
}
