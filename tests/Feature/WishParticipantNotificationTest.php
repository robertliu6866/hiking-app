<?php

namespace Tests\Feature;

use App\Actions\NotifyWishParticipants;
use App\Models\TripWish;
use App\Models\User;
use App\Notifications\WishParticipantJoined;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WishParticipantNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_line_linked_owner_and_participants_are_notified_when_a_hiker_joins(): void
    {
        Notification::fake();

        $owner = User::factory()->create(['name' => '小明', 'line_user_id' => 'U-owner']);
        $existingParticipant = User::factory()->create(['line_user_id' => 'U-existing']);
        $joiningUser = User::factory()->create(['name' => 'liuiu', 'line_user_id' => 'U-joining']);
        $wish = TripWish::create([
            'user_id' => $owner->id,
            'mountain' => '玉山主峰',
        ]);
        $wish->allUsers()->attach($existingParticipant->id, ['status' => 'joined']);
        $wish->allUsers()->attach($joiningUser->id, ['status' => 'joined']);

        app(NotifyWishParticipants::class)->handle($wish, $joiningUser);

        Notification::assertSentTo($owner, WishParticipantJoined::class, function ($notification, array $channels) use ($owner) {
            return $channels === ['database', 'line']
                && $notification->toLine($owner)['text'] === 'liuiu 參加了你的許願團「玉山主峰」。';
        });
        Notification::assertSentTo($existingParticipant, WishParticipantJoined::class);
        Notification::assertNotSentTo($joiningUser, WishParticipantJoined::class);
    }
}
