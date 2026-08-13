<?php

namespace Tests\Feature;

use App\Models\TripWish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFollowNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_and_unfollow_a_hiker(): void
    {
        $follower = User::factory()->create();
        $followed = User::factory()->create();

        $this->actingAs($follower)
            ->post(route('users.follow', $followed))
            ->assertRedirect()
            ->assertSessionHas('status', 'user-followed');

        $this->assertTrue($follower->following()->whereKey($followed->id)->exists());
        $this->assertTrue($followed->followers()->whereKey($follower->id)->exists());

        $this->actingAs($follower)
            ->delete(route('users.unfollow', $followed))
            ->assertRedirect()
            ->assertSessionHas('status', 'user-unfollowed');

        $this->assertFalse($follower->following()->whereKey($followed->id)->exists());
    }

    public function test_followers_receive_a_notification_when_followed_user_creates_wish(): void
    {
        $host = User::factory()->create(['name' => '許願山友']);
        $follower = User::factory()->create();
        $follower->following()->attach($host->id);

        $this->actingAs($host)
            ->post(route('trip-wishes.store'), [
                'mountain' => '羊頭山',
                'wished_date' => now()->addMonth()->toDateString(),
                'note' => '平日可',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'wish-created');

        $wish = TripWish::firstOrFail();
        $notification = $follower->notifications()->firstOrFail();

        $this->assertSame('followed_user_wish_created', $notification->data['type']);
        $this->assertSame($wish->id, $notification->data['wish_id']);
        $this->assertSame('許願山友 發佈了新許願，邀請你一起響應。', $notification->data['message']);
    }

    public function test_unfollowed_users_do_not_receive_invitation_notifications(): void
    {
        $host = User::factory()->create();
        $formerFollower = User::factory()->create();
        $formerFollower->following()->attach($host->id);
        $formerFollower->following()->detach($host->id);

        $this->actingAs($host)->post(route('trip-wishes.store'), [
            'mountain' => '合歡北峰',
        ]);

        $this->assertSame(0, $formerFollower->notifications()->count());
    }
}
